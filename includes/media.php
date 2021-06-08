<?php
add_filter('ajax_query_attachments_args', 'woo_tiny_verify_attachment_is_document_order', 10, 1);
add_filter('pre_get_posts', 'woo_tiny_verify_attachment_is_document_order', 10, 1);

function woo_tiny_verify_attachment_is_document_order($query)
{
    $meta_query = [
        'relation' => 'OR',
        [
            'key' => 'document_order',
            'compare' => 'NOT EXISTS'
        ],
        [
            'key' => 'document_order',
            'value' => 'yes',
            'compare' => '!='
        ]
    ];
    switch (current_action()) {
        case 'ajax_query_attachments_args':
            if (!isset($query['meta_query'])) {
                $query['meta_query'] = [];
            }
            $query['meta_query'][] = $meta_query;
            break;
        case 'pre_get_posts':
            $current_screen = '';
            if (function_exists('get_current_screen')) {
                $current_screen = get_current_screen()->id ?? '';
            }
            if ($current_screen == 'upload' && $query->get('post_type') == 'attachment') {
                $query->set('meta_query', $meta_query);
            }
            break;
        default:
            break;
    }

    return $query;
}