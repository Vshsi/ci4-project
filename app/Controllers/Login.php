<?php

namespace App\Controllers;

use App\Models\UserModel;

class Login extends BaseController
{
    // SHOW LOGIN PAGE
    public function index()
    {
        helper('form');
        return view('login');
    }

    // SHOW SIGNUP PAGE
    public function signup()
    {
        helper('form'); 
        return view('signup');
    }

    // SAVE NEW USER
    public function register()
    {
        // Get the data from the form
        $username  = $this->request->getPost('username');
        $firstName = $this->request->getPost('first_name');
        $lastName  = $this->request->getPost('last_name');
        $mobile    = $this->request->getPost('phone');
        $email     = $this->request->getPost('email');
        $paswd     = $this->request->getPost('password');

        // 1. MANUAL VALIDATION 
        if ($username == "" || $firstName == "" || $mobile == "" || $email == "" || $paswd == "") {
            $f = ($username == "") ? 'username' : (($firstName == "") ? 'first_name' : (($mobile == "") ? 'phone' : (($email == "") ? 'email' : 'password')));
            return $this->response->setJSON(['status' => 'error', 'field' => $f, 'message' => 'Required fields are missing!']);
        }

        // 2. Phone Check (10 digits)
        if (!is_numeric($mobile) || strlen($mobile) != 10) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'phone', 'message' => 'Phone must be exactly 10 digits!']);
        }

        // 3. Password Check
        if (strlen($paswd) < 5) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'password', 'message' => 'Password must be at least 5 characters!']);
        }

        // 4. Format check: filter_var is better than manual regex
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'email', 'message' => 'Invalid email format (missing .com or @)!']);
        }

        // 5. Domain check: MX Record check
        $domain = substr(strrchr($email, "@"), 1);
        
        if (!strpos($domain, '.')) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'email', 'message' => 'Email must have a domain extension (like .com or .in)!']);
        }

        if ($domain && !@checkdnsrr($domain, "MX")) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'email', 'message' => 'The domain "' . $domain . '" does not exist or has no mail server!']);
        }

        $userModel = new UserModel();

        $data = [
            'username'     => $username,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'mobile'       => $mobile,
            'email'        => $email,
            'email_id'     => $email,
            'paswd'        => password_hash($paswd, PASSWORD_DEFAULT),
            'user_type_id' => 2, // Student/User
            'active'       => 1
        ];

        // 6. Existing User Check
        $existingUser = $userModel->where('email', $email)->first();

        if ($existingUser) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'email', 'message' => 'Email already exists!']);
        }

        $userModel->save($data);

        // Tell JavaScript everything is OK
        return $this->response->setJSON(['status' => 'success', 'redirect' => site_url('login')]);
    }

    // CHECK LOGIN
    public function authenticate()
    {
        $userModel = new UserModel();

        $email = $this->request->getPost('username');
        $pass  = $this->request->getPost('password');

        $user = $userModel->where('email', $email)->orWhere('username', $email)->first();

        if ($user && password_verify($pass, $user['paswd'])) {
            session()->set([
                'username'     => $user['username'],
                'user_id'      => $user['id'], 
                'profile_image'=> $user['profile_image'],
                'user_type'    => $user['user_type_id'],
                'isLoggedIn'   => true,
            ]);
            
            return $this->response->setJSON(['status' => 'success', 'redirect' => site_url('home')]);
        } else {
            
            return $this->response->setJSON(['status' => 'error', 'field' => 'username', 'message' => 'Invalid email or password!']);
        }
    }

    // LOGOUT SESSION
    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }
}
