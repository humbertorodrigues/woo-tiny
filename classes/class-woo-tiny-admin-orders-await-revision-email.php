<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Order revision email.
 */
class Woo_Tiny_Admin_Orders_Await_Revision_Email extends WC_Email {
    private string $await_revision_message;
    /**
     * @var mixed|string|void
     */
    private $message;

    /**
     * Initialize revision template.
     */
    public function __construct() {
        $this->id               = 'orders_await_revision';
        $this->title            = 'Pedidos aguardando revisão';
        $this->description      = 'Este e-mail é enviado diariamente para lembrar os administradores dos pedidos que aguardam revisão.';
        $this->heading          = '{total_orders} pedidos estão aguardando revisão';
        $this->subject          = '[{site_title}] {total_orders} pedidos estão aguardando revisão.';
        $this->message          = 'Olá. {total_orders} pedidos estão aguardando revisão.';
        $this->await_revision_message = $this->get_option( 'await_await_revision_message', $this->message );
        $this->template_html    = 'emails/orders-await-revision.php';
        $this->template_plain   = 'emails/plain/orders-await-revision.php';
        $this->placeholders   = [
            '{total_orders}'   => 0,
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
            'await_revision_message' => array(
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
    public function get_await_revision_message() {
        return apply_filters( 'woo_tiny_order_email_await_revision_message', $this->format_string( $this->await_revision_message ), $this->object );
    }

    /**
     * Trigger email.
     *
     */
    public function trigger() {

        $orders = wc_get_orders(['status' => 'revision']);
        $this->object = $orders;
        $total_orders = count($orders);
        $this->placeholders['{total_orders}'] = $total_orders;

        if ( $this->is_enabled() && $this->get_recipient() && $total_orders > 0) {
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
            'orders'            => $this->object,
            'email_heading'    => $this->get_heading(),
            'await_revision_message' => $this->get_await_revision_message(),
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
        $message = $this->get_await_revision_message();
        $message = str_replace( '<ul>', "\n", $message );
        $message = str_replace( '<li>', "\n - ", $message );
        $message = str_replace( array( '</ul>', '</li>' ), '', $message );

        wc_get_template( $this->template_plain, array(
            'orders'            => $this->object,
            'email_heading'    => $this->get_heading(),
            'await_revision_message' => $message,
            'sent_to_admin'    => true,
            'plain_text'       => true,
            'email'            => $this,
        ), '', $this->template_base );

        return ob_get_clean();
    }
}

return new Woo_Tiny_Admin_Orders_Await_Revision_Email();
