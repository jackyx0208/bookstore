<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Sburina\Whmcs\Facades\Whmcs;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/login';
    private $name = 'successfully registered!';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // return User::create([
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'password' => Hash::make($data['password']),
        // ]);
        
        // $result = \Whmcs::GetClientsProducts([
        //     'clientid' => 8
        // ]);
        // $this->name = $result['products']['product'][0]['id'];

        // $result = \Whmcs::AddOrder([
        //     'clientid' => 11,
        //     'paymentmethod' => 'paypal',
        //     'pid' => array(498),
        // ]);
        // $this->name = $result['invoiceid'];

        // $result = \Whmcs::CreateInvoice([
        //     'userid' => 11,
        // ]);
        // $this->name = $result['invoiceid'];

        $result = \Whmcs::GetPaymentMethods([]);
        // $this->name = $result['paymentmethods']['paymentmethod'][0]["displayname"];
        $this->name = $result['result'];

        // $result = \Whmcs::AddInvoicePayment([
        //     'invoiceid' => 12688,
        //     'transid' => 'D28DJIDJW393JDWQKQI332',
        //     'gateway' => 'paypal',
        //     'date' => '2023-01-01 12:33:12',
        // ]);
        // $this->name = $result["result"];

        //  return Whmcs::AddClient([
        //     'firstname' => $data['name'],
        //     // 'lastname' => $data['name'],
        //     'address1' => $data['email'],
        //     'city' => $data['email'],
        //     'state' => $data['email'],
        //     'postcode' => $data['email'],
        //     'country' => 'us',
        //     'phonenumber' => $data['email'],
        //     'email' => $data['email'],
        //     'password2' => Hash::make($data['password']),
        // ]);
    }

    /*
     * Override the register function.
     * Make a custom registration and
     * Stop auto login after registration
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        /*$this->guard()->login($user);*/ #Restricted auto login while registration.

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath())
                ->with('success_message', $this->name);
    }



}
