<?php
/**
  Plugin Name: 	ACF to JSON
  Description: 	A plugin to map ACF fields to graphql queries
  Version: 		  0.0.1
  Author: 		  Nick Arcuri
**/

function snake_to_camel_case($input, $shouldLowerCaseFirstLetter = false, $delimiter = "_")
{
    $output = str_replace(" ", "", ucwords(str_replace($delimiter, " ", $input)));
    if ($shouldLowerCaseFirstLetter) {
        $output = lcfirst($output);
    }

    return $output;
}

function recursively_format_images($section)
{
    foreach ($section as $key => $value) {
        if ($section["type"] === "image") {
            $section["sourceUrl"] = $section["url"];
        } elseif (is_array($value)) {
            $section[$key] = recursively_format_images($value);
        }
    }

    return $section;
}

function recursively_get_post_data($section)
{
    foreach ($section as $key => $value) {
        if (is_array($value)) {
            $section[$key] = recursively_get_post_data($value);
        } elseif (is_object($value)) {
            // Found a post object?
            $featured_img = get_post_thumbnail_id($value->ID, 'full');
            $featured_img_url = wp_get_attachment_image_url(
                $featured_img,
                "full"
            );

            $author = [];
            $author_id = $value->post_author;
            $author_override = get_field("author_override", $value->ID);
            $author_override_image = get_field("author_image", $value->ID);

            if ($author_override) {
              $author["firstName"] = explode(" ", $author_override)[0];
              $author["lastName"] = explode(" ", $author_override)[1];
              $author["avatar"] = $author_override_image;
            } else {
              $author["firstName"] = get_the_author_meta(
                "user_firstname",
                $author_id
              );
              $author["lastName"] = get_the_author_meta(
                "user_lastname",
                $author_id
              );
              $author["avatar"] = array( "url" => get_avatar($author_id));
            }

            $category = get_the_category($value->ID);

            $new_post = [
                "isSticky" => is_sticky($value),
                "id" => $value->ID,
                "title" => $value->post_title,
                "uri" => wp_make_link_relative(get_permalink($value)),
                "content" => $value->post_content,
                "excerpt" => $value->post_excerpt,
                "tags" => get_the_tags($value),
                "categories" => array(
                  "edges" => array_map(function($n) {
                    return array(
                      "node" => $n
                    );
                  }, $category)
                ),
                "author" => ["node" => $author],
                "featuredImage" => [
                    "node" => [
                        "sourceUrl" => $featured_img_url,
                        "guid" => $featured_img_url,
                    ],
                ],
            ];

            $section[$key] = $new_post;
        }
    }

    return $section;
}

function recursively_format_key_casing($section)
{
    foreach ($section as $key => $value) {
        if (is_array($value)) {
            $section[
                snake_to_camel_case($key, true)
            ] = recursively_format_key_casing($value);
        } elseif (strpos($key, "_") !== false && $key !== "__typename") {
            $section[snake_to_camel_case($key, true)] = $section[$key];
            unset($section[$key]);
        }
    }

    return $section;
}

function format_flexible_content_field($field)
{
    foreach ($field as $key => $f) {
        $field[$key]["__typename"] = snake_to_camel_case($f["acf_fc_layout"]);

        if (!is_null($field[$key]["content_sections"])) {
            $field[$key]["content_sections"] = format_flexible_content_field(
                $field[$key]["content_sections"]
            );
        }
    }

    return $field;
}

add_action(
    "graphql_register_types",
    function () {
        $post_types_arr = ['Kpi'];
        $post_types = array_merge(\WPGraphQL::get_allowed_post_types(), [
            "Menu" => "Menu",
            "MenuItem" => "MenuItem",
        ]);
        // Correct formatting
        foreach($post_types as $key => $val) {
          // success is actually successStory
          if (strtolower($val) === 'success') $val = $val.'Story';
          $post_types_arr[] = snake_to_camel_case($val, false, "-");
        }

        // Acf field group schemas
        $all_fields = acf_get_field_groups();
        // Will hold Acf sub fields that belong to above
        $all_sub_fields = [];

        // Iterate through all field groups
        foreach ($all_fields as $key => $field) {
            if ($field["title"] === "Section Group") {
                continue;
            }
            // Sub fields require a function to populate
            $field["fields"] = $sub_fields = acf_get_fields($field["key"]);
            // Add to array
            $all_sub_fields[] = $field;
        }

        foreach ($post_types_arr as $post_type) {
            // Adds a rawContent field that shows without shortcodes
            $name   = 'rawPostContent';
            $config = array(
              'type'        => 'String',
              'description' => __( 'Returns the raw, unfiltered content' ),
              'resolve'     => function ( $post ) {
                $the_content = apply_filters('the_content', get_the_content($post->ID));
                $the_content = preg_replace(
                  '~(?:<[\w\d\s="_-]+>|)(?:\[/?)[^\]]+/?\](?:<\/[\w-]>|)~',
                  '',
                  $the_content
                );

                return $the_content;
              },
            );
            register_graphql_field( $post_type, $name, $config );

            // Parse ACF data and return as acfJsonField query field
            register_graphql_field($post_type, "acfJsonField", [
                "type" => "String",
                "description" => __(
                    "Example field added to the Post Type",
                    "replace-with-your-textdomain"
                ),
                "resolve" => function ($post, $args, $context, $info) use (
                    $all_sub_fields,
                    $all_fields,
                    $post_types_arr
                ) {

                    $output = [];
                    $sections = [];
                    $post_fields = get_fields($post->ID);

                    if (is_null($post_fields) || $post_fields === false) {
                        $null_return = array();
                        return json_encode($null_return);
                    }

                    $section_groups = [];
                    $section_groups["sectionGroup"] = [];
                    $section_groups["sectionGroup"][
                        "sections"
                    ] = format_flexible_content_field($post_fields["sections"]);

                    $output = $section_groups;

                    // Iterate through all field groups
                    $additional_groups = [];
                    foreach ($all_sub_fields as $key => $value) {
                        $additional_group = [];

                        // Iterate through fields of field group
                        foreach ($value["fields"] as $key2 => $sub_field) {
                            // If our post fields has an array key that matches the
                            // key of the current item
                            $field_name = $sub_field["name"];
                            $value_to_add = $post_fields[$field_name];

                            if (array_key_exists($field_name, $post_fields)) {
                                // Add a new object to our additional_group array
                                $additional_group[$field_name] =
                                    $post_fields[$field_name];
                            }
                        }

                        // If additional_group has elements, add to output
                        if (sizeof($additional_group) > 0) {
                            $additional_groups[
                                snake_to_camel_case($value["title"], false)
                            ] = $additional_group;
                        }
                    }

                    $output = array_merge($output, $additional_groups);

                    $output = recursively_get_post_data($output);

                    // switch from snake_case to camelCase
                    $output = recursively_format_key_casing($output, true);
                    /*
                     * Nests image url in:
                     * { sourceUrl: '<url>' }
                     *
                     **/
                    $output = recursively_format_images($output);

                    $output_json = json_encode($output);

                    $output_json = str_replace('acfFcLayout', '__typename', $output_json);
                    $output_json = preg_replace_callback('!__typename\\":\\"(\w+)!', function($matches) {
                      return '__typename":"'.snake_to_camel_case($matches[1]);
                    }, $output_json);

                    return $output_json;
                },
            ]);
        }

        register_graphql_field("MenuItem", "navItemFields", [
            "type" => "String",
            "description" => __(
                "Example field added to the Post Type",
                "replace-with-your-textdomain"
            ),
            "resolve" => function ($post, $args, $context, $info) {
                $locations = get_nav_menu_locations();
                $menu_item = null;

                foreach ($locations as $key => $location) {
                    $menu = wp_get_nav_menu_object($location);
                    $menu_items = wp_get_nav_menu_items($menu->term_id);

                    foreach ($menu_items as $key => $value) {
                        if ($value->title === $post->fields["label"]) {
                            $menu_item = $value;
                            break 2;
                        }
                    }
                }

                if (is_null($menu_item)) {
                    return null;
                }
                $fields = get_fields($menu_item);

                $fields = recursively_format_key_casing($fields);
                $fields = recursively_format_images($fields);

                return json_encode($fields);
            },
        ]);

        register_graphql_field("Menu", "footerFields", [
            "type" => "String",
            "description" => __(
                "Example field added to the Post Type",
                "replace-with-your-textdomain"
            ),
            "resolve" => function ($post, $args, $context, $info) {
                $locations = get_nav_menu_locations();
                $location = $locations["footer"];
                $menu_item = null;

                $menu = wp_get_nav_menu_object($location);
                $fields = get_fields($menu);

                $fields = recursively_format_key_casing($fields);
                $fields = recursively_format_images($fields);

                return json_encode($fields);
            },
        ]);
    },
    10,
    2
);
