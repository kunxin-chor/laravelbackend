<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/27/2015
 * Time: 4:18 PM
 */

namespace Darryldecode\Backend\Components\User\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\User\Models\User;
use Darryldecode\Backend\Components\User\Models\Group;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;

class QueryUsersCommand extends Command implements SelfHandling {
    /**
     * @var
     */
    private $firstName;
    /**
     * @var
     */
    private $lastName;
    /**
     * @var
     */
    private $email;
    /**
     * @var
     */
    private $groupId;
    /**
     * @var
     */
    private $orderBy;
    /**
     * @var
     */
    private $orderSort;
    /**
     * @var
     */
    private $paginated;
    /**
     * @var
     */
    private $perPage;
    /**
     * @var
     */
    private $with;

    /**
     * all arguments in associative array format
     *
     * @var array
     */
    protected $args = array();

    /**
     * query users by parameters, note that when querying by groupId, with relations is disabled
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param int $groupId
     * @param array|string $with
     * @param string $orderBy
     * @param string $orderSort
     * @param bool $paginated
     * @param int $perPage
     * @param bool $disablePermissionChecking
     */
    public function __construct($firstName = null,
                                $lastName = null,
                                $email = null,
                                $groupId = null,
                                $with = array(),
                                $orderBy = 'created_at',
                                $orderSort = 'DESC',
                                $paginated = true,
                                $perPage = 15,
                                $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->groupId = $groupId;
        $this->orderBy = $orderBy;
        $this->orderSort = $orderSort;
        $this->paginated = $paginated;
        $this->perPage = $perPage;
        $this->with = $with;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param User $user
     * @param Group $group
     * @param Dispatcher $dispatcher
     * @return CommandResult
     */
    public function handle(User $user, Group $group, Dispatcher $dispatcher)
    {
        // check user permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['user.manage']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

        // fire before query event
        $dispatcher->fire('user.beforeQuery', array($this->args));

        $results = null;

        // if group Id has been provided, we will query
        // the users by group, or else, we will query
        // users regardless of its group
        if( is_int($this->groupId) )
        {
            $q = $this->app['db']->table('user_group_pivot_table')
                ->select('*')
                ->where('group_id',$this->groupId)
                ->join('users','user_group_pivot_table.user_id','=','users.id')
                ->join('groups','user_group_pivot_table.group_id','=','groups.id');

            if( $this->paginated )
            {
                $results = $q->paginate($this->perPage);
            }
            else
            {
                $results = $q->get();
            }
        }
        else
        {
            $q = $user->with(array_merge(array('groups'),$this->with))
                ->ofFirstName($this->firstName)
                ->ofLastName($this->lastName)
                ->ofEmail($this->email);

            if( $this->paginated )
            {
                $results = $q->paginate($this->perPage);
            }
            else
            {
                $results = $q->get();
            }
        }

        // fire after query event
        $dispatcher->fire('user.afterQuery', array($results));

        // return result
        return new CommandResult(true, "Query users successful.", $results, 200);
    }
}