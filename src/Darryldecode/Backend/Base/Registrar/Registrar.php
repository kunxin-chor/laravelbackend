<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 12:42 PM
 */

namespace Darryldecode\Backend\Base\Registrar;

class Registrar {

    /**
     * @var array
     */
    protected $activeComponents = array();

    /**
     * @var array
     */
    protected $navigation = array();

    /**
     * the views
     *
     * @var array
     */
    protected $views = array();

    /**
     * the available permissions from components
     *
     * @var array
     */
    protected $availablePermissions = array();

    /**
     * the available routes from components
     *
     * @var array
     */
    protected $routes = array();

    public function __construct()
    {
        //
    }

    /**
     * adds component
     *
     * @param \Darryldecode\Backend\Base\Registrar\ComponentInterface|array $component
     * @return $this
     */
    public function addComponent($component)
    {
        if(is_array($component))
        {
            foreach($component as $c)
            {
                $this->addComponent($c);
            }
        }
        else
        {
            array_push($this->activeComponents, $component);
        }

        return $this;
    }

    /**
     * init navigations
     */
    public function initNavigation()
    {
        foreach($this->activeComponents as $component)
        {
            $component->getNavigation()->each(function($nav)
            {
                array_push($this->navigation, $nav);
            });
        }
    }

    /**
     * init permissions
     */
    public function initPermissions()
    {
        foreach($this->activeComponents as $component)
        {
            foreach($component->getAvailablePermissions() as $permission)
            {
                array_push($this->availablePermissions, $permission);
            }
        }
    }

    /**
     * init routes
     */
    public function initRoutes()
    {
        foreach($this->activeComponents as $component)
        {
            array_push($this->routes, $component->getRoutesControl());
        }
    }

    /**
     * init views
     */
    public function initViews()
    {
        foreach($this->activeComponents as $component)
        {
            array_push($this->views, $component->getViewsPath());
        }
    }

    /**
     * get navigations
     *
     * @return array
     */
    public function getNavigations()
    {
        return $this->navigation;
    }

    /**
     * get view paths
     *
     * @return array
     */
    public function getViewsPaths()
    {
        return $this->views;
    }

    /**
     * the combined permissions across registered components
     *
     * @return array
     */
    public function getAvailablePermissions()
    {
        return $this->availablePermissions;
    }

    /**
     * get routes dirs
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * get all registered active components
     *
     * @return array
     */
    public function getActiveComponents()
    {
        return $this->activeComponents;
    }
}