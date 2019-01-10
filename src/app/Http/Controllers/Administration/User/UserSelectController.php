<?php

namespace LaravelEnso\Core\app\Http\Controllers\Administration\User;

use Illuminate\Routing\Controller;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Select\app\Traits\OptionsBuilder;
use LaravelEnso\Select\app\Classes\OptionsBuilder as Builder;
use App\Http\Controllers\Auth\CsrPermission;
use App\CompanyStructure;

class UserSelectController extends Controller
{
    use OptionsBuilder;

    protected $queryAttributes = [
        'email', 'person.name', 'person.appellative',
    ];

    public function query()
    {
        return User::active()
            ->with([
                'person:id,appellative,name',
                'avatar:id,user_id',
            ]);
    }

    public function queryCs(){ 
        return CompanyStructure::query()->where('id','>=',CompanyStructure::toUser());
    }
    public function csOptions(){
        
        return  new Builder(
                $this->queryCs(),
                'id',
                ['display_name']
            );
        
    }
    public function queryCsr(){ 
        
        return \App\CompanyStructureReference::query()->whereIn('id',CsrPermission::getCsrIdArray());
    }

    public function csrOptions(){
        return  new Builder( 
            $this->queryCsr(),
            'id',
            ['name']
        );
        
    }
}
