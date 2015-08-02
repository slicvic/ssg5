<?php namespace App\Http\Controllers;

use Validator;
use Auth;
use Hash;
use Exception;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Address;
use App\Exceptions\ValidationException;
use App\Helpers\Upload;
use Flash;

/**
 * UserProfileController
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class UserProfileController extends BaseAuthController {

    /**
     * Logs out the user.
     *
     * @return Redirector
     */
    public function getLogout()
    {
        Auth::logout();

        return redirect('/');
    }

    /**
     * Displays the user's profile.
     *
     * @return Response
     */
    public function getProfile()
    {
        if ($this->user->isCustomer())
        {
            return view('user_profile.customer.show', ['user' => $this->user]);
        }

        return view('user_profile.show', ['user' => $this->user]);
    }

    /**
     * Shows the form for updating the user's profile.
     *
     * @return Response
     */
    public function getEdit()
    {
        if ($this->user->isCustomer())
        {
            return view('user_profile.customer.edit', [
                'account' => $this->user->account,
                'address' => $this->user->account->address ?: new Address
            ]);
        }

        return view('user_profile.edit', [
            'user' => $this->user
        ]);
    }

    /**
     * Updates the user's profile.
     *
     * @param  Request  $request
     * @return Redirector
     */
    public function postProfile(Request $request)
    {
        if ($this->user->isCustomer())
        {
            $this->updateCustomerProfile($request);
        }
        else
        {
            $this->updateProfile($request);
        }

        return $this->redirectWithSuccess('user/profile', 'Your profile was updated.');
    }

    /**
     * Shows the form for changing the user's password.
     *
     * @return Response
     */
    public function getPassword()
    {
        return view('user_profile.password');
    }

    /**
     * Updates the user's password.
     *
     * @param  Request  $request
     * @return Redirector
     */
    public function postPassword(Request $request)
    {
        $input = $request->all();

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_new_password' => 'required|same:new_password'
        ];

        // Validate input
        $this->validate($input, $rules);

        if ( ! Hash::check($input['current_password'], $this->user->password))
        {
            return $this->redirectBackWithError('The password you entered does not match your current one.');
        }

        // Change password
        $this->user->password = $input['new_password'];
        $this->user->save();

        return $this->redirectBackWithSuccess('Your password was changed successfully.');
    }

    /**
     * Uploads the user's photo.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function postAjaxUploadPhoto(Request $request)
    {
        $input = $request->only('file');

        // Validate input

        $validator = Validator::make($input, [
            'file' => 'required|image|mimes:gif,jpg,jpeg,png|max:' . Upload::MAX_FILE_SIZE
        ]);

        if ($validator->fails())
        {
           return response()->json(implode(' ', $validator->messages()->all(':message')), 400);
        }

        // Save photo

        try
        {
            Upload::saveUserProfilePhoto($input['file'], $this->user->id);

            $this->user->update(['has_photo' => TRUE]);

            return response()->json('Photo uploaded.');
        }
        catch(Exception $e)
        {
            $this->user->update(['has_photo' => FALSE]);

            return response()->json('Upload failed, please try again.', 500);
        }
    }

    /**
     * Updates an admin's or agent's profile.
     *
     * @param  Request  $request
     * @return void
     */
    private function updateProfile(Request $request)
    {
        $input = $request->only('user');

        $rules = [
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'firstname' => 'required',
            'lastname' => 'required'
        ];

        // Validate input
        $this->validate($input['user'], $rules);

        // Update user
        $this->user->update($input['user']);
    }

    /**
     * Updates a customer profile.
     *
     * @param  Request  $request
     * @return void
     */
    private function updateCustomerProfile(Request $request)
    {
        $input = $request->only('account', 'address');

        $rules = [
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'firstname' => 'required',
            'lastname' => 'required'
        ];

        // Validate input
        $this->validate($input['account'], $rules);

        // Update user
        $this->user->firstname = $input['account']['firstname'];
        $this->user->lastname = $input['account']['lastname'];
        $this->user->email = $input['account']['email'];
        $this->user->save();

        // Update user's account
        $account = $this->user->account;
        $account->phone = $input['account']['phone'];
        $account->mobile_phone = $input['account']['mobile_phone'];
        $account->autoship = isset($input['account']['autoship']);
        $account->save();

        // Update user's account address
        if ($account->address)
        {
            $account->address->update($input['address']);
        }
        else
        {
            $account->address()->save(new Address($input['address']));
        }
    }
}
