<?php

namespace LaravelEnso\Core\app\Models;
use Illuminate\Support\Facades\Auth;

use Illuminate\Notifications\Notifiable;
use LaravelEnso\People\app\Models\Person;
use LaravelEnso\People\app\Traits\IsPerson;
use LaravelEnso\FileManager\app\Models\File;
use LaravelEnso\RoleManager\app\Models\Role;
use LaravelEnso\FileManager\app\Traits\Uploads;
use LaravelEnso\Helpers\app\Traits\ActiveState;
use LaravelEnso\ActionLogger\app\Traits\ActionLogs;
use LaravelEnso\AvatarManager\app\Traits\HasAvatar;
use LaravelEnso\ActivityLog\app\Traits\LogsActivity;
use LaravelEnso\Core\app\Classes\DefaultPreferences;
use LaravelEnso\Impersonate\app\Traits\Impersonates;
use Illuminate\Foundation\Auth\User as Authenticatable;
use LaravelEnso\Core\app\Notifications\ResetPasswordNotification;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Helpers\RedisHelpers;
class User extends Authenticatable
{
    use ActionLogs, ActiveState, HasAvatar, Impersonates,
        IsPerson, LogsActivity, Notifiable, Uploads;

    protected $hidden = ['password', 'remember_token'];
    public $isCsrPermission = true;

    protected $fillable = [
        'person_id',
        'group_id',
        'role_id',
        'email',
        'is_active',
        'ot_id',
        'csr_id',
        'report'
    ];

    protected $casts = [
        'is_active' => 'boolean', 
        'person_id' => 'int',
        'owner_id' => 'int',
        'role_id' => 'int',
    ];

    protected $loggableLabel = 'person.name';

    protected $loggable = [
        'email',
        'group_id' => [UserGroup::class => 'name'],
        'role_id' => [Role::class => 'name'],
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function group()
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function files()
    {
        return $this->hasMany(File::class, 'created_by');
    }

    public function logins()
    {
        return $this->hasMany(Login::class);
    }

    public function preference()
    {
        return $this->hasOne(Preference::class);
    }

    public function isAdmin()
    {
        return $this->role_id === Role::AdminId;
    }

    public function isSupervisor()
    {
        return $this->role_id === Role::SupervisorId;
    }

    public function isPerson(Person $person)
    {
        return $this->person_id === $person->id;
    }

    public function persistDefaultPreferences()
    {
        $this->preference()
            ->save($this->defaultPreferences());
    }

    public function preferences()
    {
        $preferences = $this->preference
            ? $this->preference->value
            : $this->defaultPreferences()->value;

        unset($this->preference);

        return $preferences;
    }

    public function lang()
    {
        return $this->preferences()
            ->global
            ->lang;
    }

    private function defaultPreferences()
    {
        return new Preference([
            'value' => DefaultPreferences::data(),
        ]);
    }

    public function sendResetPasswordEmail()
    {
        $this->sendPasswordResetNotification(
            app('auth.password.broker')
                ->createToken($this)
        );
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function setGlobalPreferences($global)
    {
        $preferences = $this->preferences();
        $preferences->global = $global;

        $this->setPreferences($preferences);
    }

    public function setLocalPreferences($route, $value)
    {
        $preferences = $this->preferences();
        $preferences->local->$route = $value;

        $this->setPreferences($preferences);
    }

    public function delete()
    {
        try {
            parent::delete();
        } catch (\Exception $e) {
            throw new ConflictHttpException(__(
                'The user has activity in the system and cannot be deleted'
            ));
        }
    }

    private function setPreferences($preferences)
    {
        $this->preference()
            ->updateOrCreate(
                ['user_id' => $this->id],
                ['value' => $preferences]
            );
    }
    public function getCsrIdArray(){

        if(!RedisHelpers::hTryGet('user_csr_permissions'.config('database.default'),Auth::user()->csr_id,true))
        { 
            $csrIdArray=[];
            array_push($csrIdArray,Auth::user()->csr_id);

            // $csr = CompanyStructureReference::select('ot_id','o_id')->where('id',Auth::user()->csr_id)->pluck('ot_id,o_id')->toArray();
            
            $otId = \App\CompanyStructureReference::select('ot_id')->where('id',Auth::user()->csr_id)->pluck('ot_id')->first();
            $oId = \App\CompanyStructureReference::select('o_id')->where('id',Auth::user()->csr_id)->pluck('o_id')->toArray();
            
            $cs = \App\CompanyStructure::select()->where('id','>=',$otId)->get()->toArray();
            $isLast = false;
            foreach($cs as $key => $structur){
            
                isset($cs[$key+1]) ? $nextTableName = $cs[$key+1]['table_name'] : $nextTableName = false;
                $nextTableName ? $csrArray = \DB::table($nextTableName)->select('csr_id')->whereIn('parent_id',$oId)->pluck('csr_id')->toArray() : $isLast = true;
                $isLast ? null : $oId = \App\CompanyStructureReference::select('o_id')->whereIn('id',$csrArray)->pluck('o_id')->toArray();
                $isLast ? null : $csrIdArray = array_merge($csrIdArray,$csrArray);
                
            }
            // return array_push($array,Auth::user()->csr_id);
            return RedisHelpers::hSetAndReturn('user_csr_permissions'.config('database.default'),Auth::user()->csr_id,$csrIdArray,true);
        }
        return RedisHelpers::hTryGet('user_csr_permissions'.config('database.default'),Auth::user()->csr_id,true);

    }
}
