<?php

if(!function_exists('bw_get_meta_field')){
    function bw_get_meta_field($field, $post_id = 0) {
        $custom = get_post_custom($post_id);
        if (isset($custom[$field])) {
            return $custom[$field][0];
        }
        return '';
    }
}


if (!function_exists('only_numbers')) {
    function only_numbers(string $str): string
    {
        return preg_replace('/\D+/', '', $str);
    }
}

if (!function_exists('format_document')) {
    function format_document(string $document, string $type = 'cpf'): string
    {
        switch ($type) {
            case 'cep':
                $document = only_digits_cep($document);
                $document = preg_replace('/([0-9]{5})([0-9]{3})/', '$1-$2', $document);
                break;
            case 'cnpj':
                $document = only_digits_cnpj($document);
                $document = preg_replace('/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{2})/', '$1.$2.$3/$4-$5', $document);
                break;
            case 'cpf':
                $document = only_digits_cpf($document);
                $document = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})/', '$1.$2.$3-$4', $document);
                break;
            case 'nis':
                $document = only_digits_nis($document);
                $document = preg_replace('/([0-9]{3})([0-9]{5})([0-9]{2})([0-9]{1})/', '$1.$2.$3-$4', $document);
                break;
            default:
                $document = only_numbers($document);
                break;
        }
        return $document;
    }
}

if (!function_exists('format_cpf_or_cnpj')) {
    function format_cpf_or_cnpj(string $document): string
    {
        return (strlen($document) <= 11) ? format_document($document) : ((strlen($document) <= 14) ? format_document($document, 'cnpj') : $document);
    }
}

if (!function_exists('format_cep')) {
    function format_cep(string $document): string
    {
        return format_document($document, 'cep');
    }
}

if (!function_exists('validate_cpf_or_cnpj')) {
    function validate_cpf_or_cnpj(string $document): bool
    {
        return validate_document($document) || validate_document($document, 'cnpj');
    }
}

if (!function_exists('validate_cpf_or_nis')) {
    function validate_cpf_or_nis(string $document): bool
    {
        return validate_document($document) || validate_document($document, 'nis');
    }
}

if (!function_exists('validate_document')) {
    function validate_document(string $document, string $type = 'cpf'): bool
    {
        switch ($type) {
            case 'cpf':
                $document = only_digits_cpf($document);
                if (strlen($document) != 11 || preg_match('/(\d)\1{10}/', $document)) return false;
                for ($t = 9; $t < 11; $t++) {
                    for ($d = 0, $c = 0; $c < $t; $c++) {
                        $d += $document[$c] * (($t + 1) - $c);
                    }
                    $d = ((10 * $d) % 11) % 10;
                    if ($document[$c] != $d) return false;
                }
                return true;
            case 'cnpj':
                $document = only_digits_cnpj($document);
                if (strlen($document) != 14 || preg_match('/(\d)\1{13}/', $document)) return false;
                for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
                    $soma += $document[$i] * $j;
                    $j = ($j == 2) ? 9 : $j - 1;
                }
                $resto = $soma % 11;
                if ($document[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
                for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
                    $soma += $document[$i] * $j;
                    $j = ($j == 2) ? 9 : $j - 1;
                }
                $resto = $soma % 11;
                return $document[13] == ($resto < 2 ? 0 : 11 - $resto);
            case 'nis':
                $document = only_digits_nis($document);
                if ((strlen($document) != 11)
                    || (intval($document) == 0)
                ) {
                    return false;
                }
                for ($d = 0, $p = 2, $c = 9; $c >= 0; $c--, ($p < 9) ? $p++ : $p = 2) {
                    $d += $document[$c] * $p;
                }
                return ($document[10] == (((10 * $d) % 11) % 10));
            default:
                return false;
        }
    }
}

if (!function_exists('only_digits_cep')) {
    function only_digits_cep(string $document): string
    {
        return str_pad(only_numbers($document), 8, '0');
    }
}

if (!function_exists('only_digits_cpf')) {
    function only_digits_cpf(string $document): string
    {
        return str_pad(only_numbers($document), 11, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('only_digits_nis')) {
    function only_digits_nis(string $document): string
    {
        return str_pad(only_numbers($document), 11, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('only_digits_cnpj')) {
    function only_digits_cnpj(string $document): string
    {
        return str_pad(only_numbers($document), 14, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('format_phone')) {
    function format_phone(string $phone, string $type = 'br'): string
    {
        switch ($type) {
            case 'br':
                $phone = serialize_phone_br($phone);
                switch (strlen($phone)) {
                    case 11:
                        $phone = preg_replace('/([0-9]{2})([0-9]{5})([0-9]{4})/', '($1) $2-$3', $phone);
                        break;
                    case 10:
                        $phone = preg_replace('/([0-9]{2})([0-9]{4})([0-9]{4})/', '($1) $2-$3', $phone);
                        break;
                    default:
                        $phone = '';
                        break;
                }
                break;
            default:
                $phone = only_numbers($phone);
                break;
        }
        return $phone;
    }
}

if (!function_exists('serialize_phone_br')) {
    function serialize_phone_br(string $phone): string
    {
        $phone = only_numbers($phone);
        $code = '';
        if (strlen($phone) === 10) {
            $code = substr($phone, 0, 2);
            $phone = substr($phone, 2);
        }
        if (strlen($phone) === 8) {
            $digit = substr($phone, 0, 1);
            if ($digit == '8' || $digit == '9')
                $phone = $code . '9' . $phone;
            else
                $phone = $code . $phone;
        }
        return $phone;
    }
}

if (!function_exists('convert_date')) {
    function convert_date($date, string $from = 'd/m/Y', string $to = 'Y-m-d'): string
    {
        if ($date == '' || is_null($date)) return '';
        return DateTime::createFromFormat($from, $date)->format($to);
    }
}

if (!function_exists('get_first_name')) {
    function get_first_name(string $name): string
    {
        return mb_substr($name, 0, mb_strpos($name, ' '));
    }
}

if (!function_exists('get_last_name')) {
    function get_last_name(string $name): string
    {
        return mb_substr($name, mb_strrpos($name, ' ') + 1);
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $needle, string $str): bool
    {
        return strpos($str, $needle) !== false;
    }
}

if (!function_exists('array_filter_recursive')) {
    function array_filter_recursive(array $input): array
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = array_filter_recursive($value);
            }
        }
        return array_filter($input);
    }
}

if (!function_exists('validate_phone')) {
    function validate_phone(string $phone): bool
    {
        return strlen(serialize_phone_br($phone)) >= 10;
    }
}

if(!function_exists('set_alert')){
    function set_alert($class = 'info', $message = ''): string
    {
        $alert = ['class' => $class, 'message' => $message];
        return '?' . http_build_query($alert);
    }
}

if(!function_exists('wc_serialize_br_address')){
    function wc_serialize_br_address($address, $type = '')
    {
        $field_defaults = [
            'billing' => [
                'first_name' => '',
                'last_name' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'state' => '',
                'postcode' => '',
                'country' => 'BR',
                'email' => '',
                'phone' => '',
                /* Extra fields br */
                'neighborhood' => '',
                'number' => '',
                'cellphone' => '',
                'cpf' => '',
                'cnpj' => '',
                'rg' => '',
                'ie' => '',
                'persontype' => '',
            ],
            'shipping' => [
                'first_name' => '',
                'last_name' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'state' => '',
                'postcode' => '',
                'country' => 'BR',
                /* Extra fields br */
                'neighborhood' => '',
                'number' => '',
            ],
        ];

        if(empty($address)) return $field_defaults;

        if($type == ''){
            return [
                'billing' => wp_parse_args($address, $field_defaults['billing']),
                'shipping' => wp_parse_args($address, $field_defaults['shipping'])
            ];
        }
        return wp_parse_args($address, $field_defaults[$type]);
    }
}

if(!function_exists('get_custom_product_price_by_user_id')){
    function get_custom_product_price_by_user_id($user_id, $product_id, $channel_id){
        $data = get_user_meta($user_id, 'bw_custom_product_prices', true);
        foreach ($data as $item){
            if($item['product_id'] == $product_id && $item['channel_id'] == $channel_id){
                return $item['new_price'];
            }
        }
        return false;
    }
}