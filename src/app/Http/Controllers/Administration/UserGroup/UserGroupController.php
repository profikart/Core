<?php

namespace LaravelEnso\Core\app\Http\Controllers\Administration\UserGroup;

use Illuminate\Routing\Controller;
use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\Core\app\Forms\Builders\UserGroupForm;
use LaravelEnso\Core\app\Http\Requests\ValidateUserGroupRequest;

class UserGroupController extends Controller
{
    public function create(UserGroupForm $form)
    {
        return ['form' => $form->create()];
    }

    public function store(ValidateUserGroupRequest $request, UserGroup $userGroup)
    {
        $data = $request->validated();
        $data['display_to'] = json_encode(explode(',',$data['display_to']));
        $userGroup = $userGroup->storeWithRoles($data);

        return [
            'message' => __('The user group was successfully created'),
            'redirect' => 'administration.userGroups.edit',
            'param' => ['userGroup' => $userGroup->id],
        ];
    }

    public function edit(UserGroup $userGroup, UserGroupForm $form)
    {
        $userGroup->display_to = implode(',',json_decode($userGroup->display_to,true));
        return ['form' => $form->edit($userGroup)];
    }

    public function update(ValidateUserGroupRequest $request, UserGroup $userGroup)
    {
        $data = $request->validated();
        $data['display_to'] = json_encode(explode(',',$data['display_to']));

        $userGroup->updateWithRoles($data);

        return ['message' => __('The user group was successfully updated')];
    }

    public function destroy(UserGroup $userGroup)
    {
        $userGroup->delete();

        return [
            'message' => __('The user group was successfully deleted'),
            'redirect' => 'administration.userGroups.index',
        ];
    }
}
