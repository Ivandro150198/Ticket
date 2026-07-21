<?php

class AuthController
{
    public function loginForm(): void
    {
        if (auth_check()) {
            redirect('');
        }
        view('auth/login', [
            'title' => 'Entrar',
            'seo' => [
                'title' => 'Entrar',
                'description' => 'Aceda à sua conta EventTicket-GB.',
                'robots' => 'noindex,nofollow',
                'canonical' => absolute_url('login'),
            ],
        ]);
    }

    public function login(): void
    {
        verify_csrf();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (RateLimit::tooManyLoginAttempts($ip)) {
            flash('error', 'Demasiadas tentativas. Aguarde 15 minutos.');
            redirect('login');
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            RateLimit::hitLogin($ip, $email);
            store_old(['email' => $email]);
            flash('error', 'E-mail ou palavra-passe incorretos.');
            redirect('login');
        }
        RateLimit::clearLogin($ip);
        unset($user['password']);
        $_SESSION['user'] = $user;
        if (!empty($_POST['remember'])) {
            session_regenerate_id(true);
        }
        clear_old();
        AuditService::log('auth.login', 'user', (int) $user['id']);
        flash('success', 'Bem-vindo, ' . $user['name'] . '!');
        if ($user['role'] === 'admin') {
            redirect('admin');
        }
        if ($user['role'] === 'produtor') {
            redirect('produtor');
        }
        redirect('conta');
    }

    public function registerForm(): void
    {
        if (auth_check()) {
            redirect('');
        }
        view('auth/register', [
            'title' => 'Criar conta',
            'seo' => [
                'title' => 'Criar conta',
                'description' => 'Crie a sua conta EventTicket-GB para comprar bilhetes.',
                'robots' => 'noindex,nofollow',
                'canonical' => absolute_url('registo'),
            ],
        ]);
    }

    public function register(): void
    {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $accept = !empty($_POST['accept']);

        store_old(compact('name', 'email', 'phone'));

        if ($name === '' || $email === '' || strlen($password) < 6 || !$accept) {
            flash('error', 'Preencha todos os campos e aceite os termos. Palavra-passe mín. 6 caracteres.');
            redirect('registo');
        }
        if (User::findByEmail($email)) {
            flash('error', 'Já existe uma conta com este e-mail.');
            redirect('registo');
        }

        $id = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => 'cliente',
        ]);
        $user = User::find($id);
        $_SESSION['user'] = $user;
        clear_old();
        flash('success', 'Conta criada com sucesso.');
        redirect('');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        flash('success', 'Sessão terminada.');
        redirect('');
    }

    public function forgotForm(): void
    {
        view('auth/forgot', [
            'title' => 'Recuperar palavra-passe',
            'seo' => ['title' => 'Recuperar palavra-passe', 'robots' => 'noindex,nofollow', 'description' => 'Recupere o acesso à sua conta EventTicket-GB.'],
        ]);
    }

    public function forgot(): void
    {
        verify_csrf();
        $email = trim($_POST['email'] ?? '');
        $user = User::findByEmail($email);
        if ($user) {
            $token = PasswordReset::create($email);
            $link = absolute_url('repor-senha?token=' . urlencode($token));
            MailService::send(
                $email,
                'Recuperar palavra-passe EventTicket-GB',
                '<p>Clique para repor a palavra-passe:</p><p><a href="' . e($link) . '">' . e($link) . '</a></p><p>Válido 1 hora.</p>'
            );
        }
        flash('success', 'Se o e-mail existir, enviámos um link de recuperação.');
        redirect('recuperar');
    }

    public function resetForm(): void
    {
        $token = $_GET['token'] ?? '';
        $row = $token ? PasswordReset::findValid($token) : null;
        if (!$row) {
            flash('error', 'Link inválido ou expirado.');
            redirect('recuperar');
        }
        view('auth/reset', [
            'title' => 'Nova palavra-passe',
            'token' => $token,
            'seo' => ['title' => 'Nova palavra-passe', 'robots' => 'noindex,nofollow', 'description' => 'Defina uma nova palavra-passe.'],
        ]);
    }

    public function reset(): void
    {
        verify_csrf();
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $row = PasswordReset::findValid($token);
        if (!$row || strlen($password) < 6) {
            flash('error', 'Pedido inválido ou palavra-passe demasiado curta.');
            redirect('recuperar');
        }
        User::updatePassword($row['email'], $password);
        PasswordReset::deleteByEmail($row['email']);
        flash('success', 'Palavra-passe atualizada. Pode entrar.');
        redirect('login');
    }
}
