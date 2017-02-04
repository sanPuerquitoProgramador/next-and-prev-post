<?php namespace PolloZen\NextPrevPost;

use Backend;
use System\Classes\PluginBase;

/**
 * NextPrevPost Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = ['RainLab.Blog','VojtaSvoboda.TwigExtensions'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Next and Prev Post',
            'description' => 'Retrieve the next and prev post from the current post',
            'author'      => 'PolloZen',
            'icon'        => 'icon-arrows-h'
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'PolloZen\NextPrevPost\Components\NextPrev' => 'nextAndPrev',
        ];
    }

}
