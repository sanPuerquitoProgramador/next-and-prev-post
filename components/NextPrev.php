<?php namespace PolloZen\NextPrevPost\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Blog\Models\Post;
use RainLab\Blog\Models\Category;

class NextPrev extends ComponentBase
{
    public $nextPost;
    public $prevPost;
    public function componentDetails()
    {
        return [
            'name'        => 'Next and Prev Post',
            'description' => 'Retrieve the next and prev post from the current post'
        ];
    }

    public function defineProperties()
    {
        return [
            'category' =>[
                'title'         => 'Category',
                'description'   => 'Filter posts by category. If no category selectec, the results will be the inmediate next and previous post',
                'type'          => 'dropdown',
                'default'       => 'current',
                'placeholder'   => 'Select a category',
                'showExternalParam' => false
            ],
            'postPage' => [
                'title'         => 'Post page',
                'description'   => 'Page to show linked posts',
                'type'          => 'dropdown',
                'default'       => 'blog/post',
                'group'         => 'Links',
            ]
        ];
    }

    /**
     * [getPostPageOptions]
     * @return [array][Blog]
     */
    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * [getCategoryOptions]
     * @return [array list] [Blog Categories]
     */
    public function getCategoryOptions(){
        $categories =  array('current' => 'Current post category','noFilter' => 'No category filter') + Category::orderBy('name')->lists('name','id');
        return $categories;
    }

    /**
     * prepare Vars function
     * @return [object]
     */
    protected function prepareVars()
    {
        $this->postParam = $this->page['postParam'] = $this->property('postParam');
        /* Get post page */
        $this->postPage = $this->property('postPage') ? $this->property('postPage') : '404';
    }

    public function onRun(){
        $this->prepareVars();
        $this->nextPost = $this->page['nextPost'] = $this->getNP('Next');
        $this->prevPost = $this->page['prevPost'] = $this->getNP('Prev');
    }

    public function getNP($pp){
        $params = ($pp=='Next') ? array(0 =>'>',1 => 'asc') : array(0 => '<', 1 => 'desc');
        $currentPostId =  $this->getPostId();
        $category = $this->getCategory();

       if($currentPostId != 0){
            $post =  Post::isPublished();
                $post   ->where('id',$params[0],$currentPostId)
                        ->orderBy('id',$params[1]);
                if ($category !== null) {
                    if (!is_array($category)) $category = [$category];
                    $post->whereHas('categories', function($q) use ($category) {
                        $q->whereIn('id', $category);
                    });
                }
            $np = $post->first();

            /* Agregamos el helper de la URL */
            if(isset($np->attributes)){
                $np->setUrl($this->postPage,$this->controller);
            }

            return $np;
       }
    }

    /**
     * getPostID function
     * @return [function] return the current post id
     */
    protected function getPostId(){
        if($this->page[ 'post' ]){
            if($this->page[ 'post' ]->id){
                return $this->page['post']->id;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * getCategory
     * @return protected function returns the current category or the category filter selected by the user or null
     */
    protected function getCategory(){
        $category = null;
        if($this->property('category')=='current'){
            $category = $this->page[ 'post' ]->categories[0]->id;
        } elseif($this->property('category')=='noFilter'){
            $category = null;
        } else {
            $category = $this->property('category');
        }
        return $category;
    }
}
