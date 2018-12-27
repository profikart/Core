<?php

namespace LaravelEnso\Core\app\Http\Controllers\Administration\User;

use Illuminate\Routing\Controller;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\People\app\Models\Person;
use LaravelEnso\Core\app\Classes\ProfileBuilder;
use LaravelEnso\Core\app\Forms\Builders\UserForm;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use LaravelEnso\Core\app\Http\Requests\ValidateUserRequest;

class UserController extends Controller
{
    use AuthorizesRequests; 
    protected $personFields= ['title','name','appellative','email','uid','phone','birthday','gender','obs'];
    public function create(UserForm $form)
    {
        return ['form' => $form->create()];
    }

    public function store(ValidateUserRequest $request)
    {
        $personData =[];
        $data=$request->all();
        foreach($this->personFields as $key => $v){
            $personData=array_merge($personData,[$v =>$data[$v]]);
            unset($data[$v]);
        }
        $data= array_merge($data,['email'=>$request->all()['email']]);
        $person = Person::create($personData);
        $data['person_id'] = $person->id;
        $user = new User($data);

        $this->authorize('handle', $user);
        $user->save();

        $user->sendResetPasswordEmail();

        return [
            'message' => __('The user was successfully created'),
            'redirect' => 'administration.users.edit',
            'param' => ['user' => $user->id],
        ];

    }

    public function show(User $user)
    {
        (new ProfileBuilder($user))->set();

        return ['user' => $user];
    }

    public function edit(User $user, UserForm $form)
    {
        return ['form' => $form->edit($user)];
    }

    public function update(ValidateUserRequest $request, User $user)
    {
        $personData =[];
        $data=$request->all();
        foreach($this->personFields as $key => $v){
            $personData=array_merge($personData,[$v =>$data[$v]]);
            unset($data[$v]);
        }
        $data= array_merge($data,['email'=>$request->all()['email']]);
        
        Person::where('id',$user->person_id)->update($personData);
        $this->authorize('handle', $user);
 
        if ($request->filled('password')) {
            $this->authorize('change-password', $user);
            $user->password = bcrypt($request->get('password'));
        }

        if ($request->get('role_id') !== $user->role_id) {
            $this->authorize('change-role', $user);
        }
        $user->update($request->all());

        return ['message' => __('The user was successfully updated')];
    }

    public function destroy(User $user)
    {
        $this->authorize('handle', $user);

        $user->delete();

        return [
            'message' => __('The user was successfully deleted'),
            'redirect' => 'administration.users.index',
        ];
    }
}
