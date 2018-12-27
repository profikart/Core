<?php

namespace LaravelEnso\Core\app\Http\Controllers\Administration\User;

use Illuminate\Routing\Controller;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Select\app\Traits\OptionsBuilder;
use LaravelEnso\Select\app\Classes\OptionsBuilder as Builder;

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
        return CompanyStructure::query();
    }
    public function csOptions(){
        return  new Builder(
            $this->queryCs(),
            'id',
            ['display_name']
        );
        
    }
    public function queryCsr(){ 
        // return \App\Central::query()->select();
        return \App\CompanyStructureReference::query();
    }
    public function csrOptions(){
        // dump($this->queryCsr()->get());
        // dump(json_decode($_GET['pivotParams'],true)['references']['id']);
        return  new Builder( 
            $this->queryCsr(),
            'id',
            ['name']
        );
        
    }
}
