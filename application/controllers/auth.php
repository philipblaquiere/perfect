<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller
{
	/**
	 * Constructor: initialize required libraries.
	 */

	public function __construct()
  {
    parent::__construct();
    $this->load->model('user_model');
    $this->load->model('system_message_model');
  }

  public function index()
  {
    $this->login();
  }

  public function login()
  {
    if ($this->is_logged_in())
    {
      redirect('home', 'location');
    }
    $data = array('page_title' => 'Sign In');
    $data['page'] = "login";
    $this->view_wrapper('sign_in', $data, false);
  }

  public function sign_in()
  {
    $this->require_not_login();

    $this->load->library('form_validation');

    $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

    if($this->form_validation->run() == FALSE)
    {
      $data['page'] = "login";
      $this->view_wrapper('sign_in',$data);
    }
    else
    {
      //get sign in form data
      $email = $this->input->post('email');
      $password = $this->input->post('password');

      $user = $this->user_model->get($email);
      if(!$user)
      {
        $this->system_message_model->set_message('There is an error in your email or password', MESSAGE_INFO);
        redirect('home', 'location');
      }
      else if($this->_validate_password($user,$password)) 
      {
        $this->user_model->log_login($user['id']);
        $this->set_user($user);
        redirect('summoner/'.$user['id'], 'location');
      }
      else
      {
        $this->system_message_model->set_message('There is an error in your email or password', MESSAGE_INFO);
        redirect('home', 'location');
      }
    }
  }
  
  public function sign_out()
  {
    $this->require_login();
    $this->destroy_session();
    redirect('home', 'location');
  }

  public function reset_password()
  {
    $content = $_POST;
    $user = $this->user_model->get(strtolower(trim($content['email'])));
    if(empty($user))
    {
      return;
    }

  }

  public function forgot()
  {
    $data['page'] = "forgot";
    $this->view_wrapper('forgot_password', $data);
  }

  private function _validate_password($user,$password)
  {
    if(!$password || !$user['email'])
      return false;
    return $user['password'] === $this->password_hash($password);
  }
}