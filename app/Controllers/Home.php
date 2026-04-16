<?php

namespace App\Controllers;

use App\Models\UserModel;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        
        $data = [
            'users' => $userModel->select('id, username, first_name, last_name, mobile, email, email_id, paswd, profile_image, active, designation_id')->paginate(10),
            'pager' => $userModel->pager,
        ];

        return view('admin/dashboard', $data); 
    }

    public function profile()
    {
        if (!session()->get('username')) {
            return redirect()->to('/login');
        }

        $userModel = new \App\Models\UserModel();
        $data = [
            'currentUser' => $userModel->select('id, username, first_name, last_name, mobile, email, email_id, paswd, profile_image, active, designation_id')->find(session()->get('user_id'))
        ];

        return view('admin/profile', $data);
    }

    public function userList()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userModel = new \App\Models\UserModel();
        $data = [
            'users' => $userModel->select('id, username, first_name, last_name, mobile, email, email_id, profile_image, designation_id, active')->paginate(10),
            'pager' => $userModel->pager
        ];

        return view('admin/user_list', $data);
    }

    public function addUser()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('admin/add_user');
    }

    public function saveUser()
    {
        $username   = $this->request->getPost('username');
        $email      = $this->request->getPost('email');
        $paswd      = $this->request->getPost('password');
        $mobile     = $this->request->getPost('phone');
        $firstName  = $this->request->getPost('first_name');
        $lastName   = $this->request->getPost('last_name');

        // 1. EMPTY CHECK
        if (empty($username) || empty($email) || empty($paswd) || empty($mobile)) {
            $f = empty($username) ? 'username' : (empty($email) ? 'email' : (empty($mobile) ? 'phone' : 'password'));
            return $this->response->setJSON(['status' => 'error', 'field' => $f, 'message' => 'Please fill this field!']);
        }

        // 2. PHONE VALIDATION
        if (!is_numeric($mobile) || strlen($mobile) != 10) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'phone', 'message' => 'Phone must be exactly 10 digits!']);
        }

        // 3. USERNAME FORMAT
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'field'   => 'username',
                'message' => 'Username must be letters and numbers only!'
            ]);
        }

        // 3. EMAIL FORMAT
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'field'   => 'email',
                'message' => 'Please enter a valid email!'
            ]);
        }

        // 4. DOMAIN CHECK (DNS)
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            return $this->response->setJSON([
                'status'  => 'error',
                'field'   => 'email',
                'message' => 'This email domain does not exist!'
            ]);
        }

        $userModel = new \App\Models\UserModel();

        // 5. UNIQUENESS CHECK
        if ($userModel->select('id')->where('email', $email)->first()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'field'   => 'email',
                'message' => 'This email is already registered!'
            ]);
        }

        // 6. SAVE
        $userModel->save([
            'username'   => $username,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'mobile'     => $mobile, 
            'email'      => $email,
            'email_id'   => $email, // Sync with teacher's other email field
            'paswd'      => password_hash($paswd, PASSWORD_DEFAULT),
            'user_type_id' => 2, // Default to user type
            'active'     => 1
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
            'redirect' => site_url('home/userList')
        ]);
    }

    public function logout()
    {
        // 3. Destroy the "VIP Pass" (Session)
        session()->destroy();
        // Go back to login
        return redirect()->to('/login');
    }

    public function updateAccount()
    {
        $id = session()->get('user_id'); // Get the ID from memory
        $newName = $this->request->getPost('new_username');

        if ($id == null) {
            // If ID is empty, we are not logged in!
            return redirect()->to('/login');
        } else {
            // If we have an ID, we update the name
            $userModel = new \App\Models\UserModel();
            
            // 1. Save new name to database
            $userModel->update($id, ['username' => $newName]);

            // 2. Update memory (Session) so the UI shows the new name
            session()->set('username', $newName);

            return redirect()->to('/home');
        }
    }

    public function deleteAccount()
    {
        $id = session()->get('user_id'); // 1. Get your ID from memory

        if ($id == null) {
            return redirect()->to('/login');
        } else {
            $userModel = new \App\Models\UserModel();
            
            // 2. DELETE your row from the Database
            $userModel->delete($id);

            // 3. Clear your memory (Logout)
            session()->destroy();

            // 4. Go back to login with a message
            return redirect()->to('/login');
        }
    }

    // --- ADMIN ACTIONS (NEW) ---

    // 1. Show the Edit Page
    public function editUser($id)
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->select('id, username, first_name, last_name, mobile, email, email_id, paswd, profile_image, designation_id')->find($id); // Get just THIS user

        if (!$user) {
            return redirect()->to('/home');
        }

        return view('admin/edit_user', ['user' => $user]);
    }

    // 2. Save the Update
    public function saveUserUpdate($id)
    {
        $userModel = new \App\Models\UserModel();
        $username   = $this->request->getPost('username');
        $firstName  = $this->request->getPost('first_name');
        $lastName   = $this->request->getPost('last_name');
        $mobile     = $this->request->getPost('phone');
        $email      = $this->request->getPost('email');

        // 1. BLANK CHECKS
        if (empty($username) || empty($firstName) || empty($mobile) || empty($email)) {
            $f = empty($username) ? 'username' : (empty($firstName) ? 'first_name' : (empty($mobile) ? 'phone' : 'email'));
            return $this->response->setJSON(['status' => 'error', 'field' => $f, 'message' => 'This field cannot be blank!']);
        }

        // 2. PHONE VALIDATION
        if (!is_numeric($mobile) || strlen($mobile) != 10) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'phone', 'message' => 'Phone must be exactly 10 digits!']);
        }

        // 3. EMAIL FORMAT
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'email', 'message' => 'Invalid email format!']);
        }

        // 4. DOMAIN CHECK
        $domain = substr(strrchr($email, "@"), 1);
        if (!@checkdnsrr($domain, "MX")) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'email', 'message' => 'Email domain is invalid!']);
        }

        // 5. UNIQUE CHECK
        $existing = $userModel->select('id')->where('email', $email)->where('id !=', $id)->first();
        if ($existing) {
            return $this->response->setJSON(['status' => 'error', 'field' => 'email', 'message' => 'Email is already in use by someone else!']);
        }

        $updateData = [
            'username'   => $username,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'mobile'     => $mobile,
            'email'      => $email,
            'email_id'   => $email
        ];

        // 6. PHOTO UPLOAD LOGIC (FILESYSTEM)
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/profiles', $newName);
            $updateData['profile_image'] = $newName;
        }

        // SAVE CHANGES
        $userModel->update($id, $updateData);

        // Sync session if the logged-in user edited THEIR OWN profile
        if ($id == session()->get('user_id')) {
            session()->set('username', $username);
            if (isset($newName)) {
                session()->set('profile_image', $newName);
            }
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'redirect' => site_url('home')
        ]);
    }

    // 3. Admin Delete
    public function deleteUser($id)
    {
        $userModel = new \App\Models\UserModel();
        $userModel->delete($id);
        
        return redirect()->to('/home/userList');
    }

    public function viewTemplate($page)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Check if the file exists before trying to load it
        if (!file_exists(APPPATH . 'Views/admin/' . $page . '.php')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/' . $page);
    }
}
