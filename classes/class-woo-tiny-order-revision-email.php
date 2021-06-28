<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Order revision email.
 */
class Woo_Tiny_Order_Revision_Email extends WC_Email {
    private $revision_message;
    /**
     * @var mixed|string|void
     */
    private $message;

    /**
     * Initialize revision template.
     */
    public function __construct() {
        $this->id               = 'in_revision';
        $this->title            = 'Pedido em revisão';
        $this->description      = 'Este e-mail é enviado quando um pedido é criado por um vendedor.';
        $this->heading          = 'Seu pedido está aguardando revisão';
        $this->subject          = '[{site_title}] Seu pedido {order_number} está aguardando revisão';
        $this->message          = 'Olá. Seu pedido recente em {site_title} está aguardando revisão.';
        $this->revision_message = $this->get_option( 'revision_message', $this->message );
        $this->template_html    = 'emails/order-revision.php';
        $this->template_plain   = 'emails/plain/order-revision.php';
        $this->placeholders   = [
            '{order_date}'   => '',
            '{order_number}' => '',
        ];
        $this->recipient = $this->get_option('recipient', '');
        // Call parent constructor.
        parent::__construct();

        $this->template_base = WOO_TINY_DIR . 'templates/';
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Habilitar/Desabilitar',
                'type'    => 'checkbox',
                'label'   => 'Habilite esta notificação por e-mail',
                'default' => 'yes',
            ),
            'recipient'          => array(
                'title'       => 'Destinatário(s)',
                'type'        => 'text',
                'description' => 'Insira os destinatários (separados por vírgula) para este e-mail. O padrão é Vendedor.',
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject' => array(
                'title'       => 'Assunto',
                'type'        => 'text',
                'description' => sprintf('Isso controla a linha de assunto do email. Deixe em branco para usar o assunto padrão: <code>%s</code>.', $this->subject ),
                'placeholder' => $this->subject,
                'default'     => '',
                'desc_tip'    => true,
            ),
            'heading' => array(
                'title'       => 'Cabeçalho',
                'type'        => 'text',
                'description' => sprintf('Isso controla o título principal contido no e-mail. Deixe em branco para usar o título padrão: <code>%s</code>.', $this->heading ),
                'placeholder' => $this->heading,
                'default'     => '',
                'desc_tip'    => true,
            ),
            'revision_message' => array(
                'title'       => 'Conteúdo',
                'type'        => 'textarea',
                'description' => sprintf('Isso controla o conteúdo inicial do e-mail. Deixe em branco para usar o conteúdo padrão: <code>%s</code>.', $this->message ),
                'placeholder' => $this->message,
                'default'     => '',
                'desc_tip'    => true,
            ),
            'email_type' => array(
                'title'       => 'Tipo',
                'type'        => 'select',
                'description' => 'Escolha qual formato de e-mail enviar.',
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_custom_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Email type options.
     *
     * @return array
     */
    protected function get_custom_email_type_options() {
        if ( method_exists( $this, 'get_email_type_options' ) ) {
            return $this->get_email_type_options();
        }

        $types = array( 'plain' => __( 'Plain text', 'woo-tiny' ) );

        if ( class_exists( 'DOMDocument' ) ) {
            $types['html']      = __( 'HTML', 'woo-tiny' );
            $types['multipart'] = __( 'Multipart', 'woo-tiny' );
        }

        return $types;
    }

    /**
     * Get email revision message.
     *
     * @return string
     */
    public function get_revision_message() {
        return apply_filters( 'woo_tiny_order_email_revision_message', $this->format_string( $this->revision_message ), $this->object );
    }

    /**
     * Trigger email.
     *
     * @param  int      $order_id      Order ID.
     * @param  WC_Order $order         Order data.
     */
    public function trigger( $order_id, $order = false) {
        if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }

        if (is_a( $order, 'WC_Order' )) {
            $this->object = $order;
            $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        }

        $seller_email = woo_tiny_get_seller_data_by_order_id($order_id, 'user_email');
        if ($seller_email) {
            if(!$this->recipient){
                $this->recipient = $seller_email;
            }else {
                $this->recipient .= ', ' . $seller_email;
            }
        }
        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        }
    }

    /**
     * Get content HTML.
     *
     * @return string
     */
    public function get_content_html() {
        ob_start();

        wc_get_template( $this->template_html, array(
            'order'            => $this->object,
            'email_heading'    => $this->get_heading(),
            'revision_message' => $this->get_revision_message(),
            'sent_to_admin'    => true,
            'plain_text'       => false,
            'email'            => $this,
        ), '', $this->template_base );

        return ob_get_clean();
    }

    /**
     * Get content plain text.
     *
     * @return string
     */
    public function get_content_plain() {
        ob_start();

        // Format list.
        $message = $this->get_revision_message();
        $message = str_replace( '<ul>', "\n", $message );
        $message = str_replace( '<li>', "\n - ", $message );
        $message = str_replace( array( '</ul>', '</li>' ), '', $message );

        wc_get_template( $this->template_plain, array(
            'order'            => $this->object,
            'email_heading'    => $this->get_heading(),
            'revision_message' => $message,
            'sent_to_admin'    => true,
            'plain_text'       => true,
            'email'            => $this,
        ), '', $this->template_base );

        return ob_get_clean();
    }
}

return new Woo_Tiny_Order_Revision_Email();
