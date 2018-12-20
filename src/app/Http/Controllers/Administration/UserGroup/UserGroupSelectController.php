<?php

namespace LaravelEnso\Core\app\Http\Controllers\Administration\UserGroup;

use Illuminate\Routing\Controller;
use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\Select\app\Traits\OptionsBuilder;
use LaravelEnso\Select\app\Classes\OptionsBuilder as Builder;

use App\CompanyStructureReference;

class UserGroupSelectController extends Controller
{
    use OptionsBuilder;

    protected $model = UserGroup::class;

    public function query(){
        return $this->model::query()->whereRaw("JSON_SEARCH(display_to,'all',".CompanyStructureReference::getOtID(\Auth::user()->csr_id).") IS NOT NULL");
    }  

}
 