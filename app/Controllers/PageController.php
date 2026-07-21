<?php

class PageController
{
    private function page(string $viewName, string $title, string $description, string $path, array $extra = []): void
    {
        view($viewName, array_merge($extra, [
            'title' => $title,
            'seo' => [
                'title' => $title,
                'description' => $description,
                'canonical' => absolute_url($path),
                'jsonld' => [organization_jsonld()],
            ],
        ]));
    }

    public function contact(): void
    {
        $this->page('pages/contact', 'Contacto', 'Fale connosco: suporte EventTicket-GB, produtores e questões sobre bilhetes.', 'contacto');
    }

    public function contactSubmit(): void
    {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
            flash('error', 'Preencha os campos obrigatórios.');
            redirect('contacto');
        }
        ContactMessage::create(compact('name', 'email', 'subject', 'message'));
        flash('success', 'Mensagem enviada. Responderemos em breve.');
        redirect('contacto');
    }

    public function faq(): void
    {
        $faqs = Faq::all();
        $entities = [];
        foreach ($faqs as $faq) {
            $entities[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }
        view('pages/faq', [
            'title' => 'Perguntas frequentes',
            'faqs' => $faqs,
            'seo' => [
                'title' => 'Perguntas frequentes',
                'description' => 'Respostas sobre bilhetes, pagamentos, código QR, validação na porta e conta EventTicket-GB.',
                'canonical' => absolute_url('faq'),
                'jsonld' => [
                    organization_jsonld(),
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'FAQPage',
                        'mainEntity' => $entities,
                    ],
                ],
            ],
        ]);
    }

    public function services(): void
    {
        $this->page(
            'pages/services',
            'Serviços para produtores',
            'Publique eventos, venda bilhetes online e valide entradas com QR. Plataforma para produtores de eventos.',
            'servicos'
        );
    }

    public function terms(): void
    {
        $this->page('pages/terms', 'Termos e Condições', 'Termos de utilização e compra de bilhetes na EventTicket-GB.', 'termos');
    }

    public function privacy(): void
    {
        $this->page('pages/privacy', 'Política de Privacidade', 'Como a EventTicket-GB trata os seus dados pessoais (RGPD).', 'privacidade');
    }

    public function ral(): void
    {
        $this->page(
            'pages/ral',
            'Resolução Alternativa de Litígios',
            'Informação sobre entidades de Resolução Alternativa de Litígios (RAL) para consumidores.',
            'resolucao-litigios'
        );
    }

    public function cookies(): void
    {
        $this->page('pages/cookies', 'Política de Cookies', 'Cookies essenciais e preferências na EventTicket-GB.', 'cookies');
    }

    public function outlets(): void
    {
        $this->page('pages/outlets', 'Postos de Venda', 'Onde comprar bilhetes EventTicket-GB: online e postos físicos de exemplo.', 'postos-de-venda');
    }

    public function newsletter(): void
    {
        verify_csrf();
        $email = trim($_POST['email'] ?? '');
        $accept = !empty($_POST['accept']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$accept) {
            flash('error', 'Indique um e-mail válido e aceite receber novidades.');
            redirect('');
        }
        if (Newsletter::subscribe($email)) {
            flash('success', 'Subscrição efetuada com sucesso. Obrigado!');
        } else {
            flash('success', 'Este e-mail já está subscrito.');
        }
        redirect('');
    }

    public function complaints(): void
    {
        $this->page(
            'pages/complaints',
            'Livro de Reclamações',
            'Registe uma reclamação sobre a EventTicket-GB. Livro de reclamações eletrónico.',
            'livro-de-reclamacoes'
        );
    }

    public function complaintsSubmit(): void
    {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nif = trim($_POST['nif'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $message === '') {
            flash('error', 'Preencha os campos obrigatórios.');
            redirect('livro-de-reclamacoes');
        }
        $id = Complaint::create(compact('name', 'email', 'nif', 'subject', 'message'));
        MailService::send(
            env('MAIL_FROM', 'noreply@eventticket-gb.local'),
            'Nova reclamação #' . $id,
            '<p>De: ' . e($name) . ' (' . e($email) . ')</p><p>' . nl2br(e($message)) . '</p>'
        );
        AuditService::log('complaint.created', 'complaint', $id);
        flash('success', 'Reclamação registada com o nº ' . $id . '.');
        redirect('livro-de-reclamacoes');
    }
}
