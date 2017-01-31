<?php namespace PolloZen\NextPrevPost\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Blog\Models\Post;
use RainLab\Blog\Models\Category;

class NextPrev extends ComponentBase
{
    public $next;
    public $prev;
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
                'description'   => 'Filter result by category. If no category selectec, the results will be the inmediate next and previous post',
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
    }

    public function onRun(){
        $this->prepareVars();

        /*Get the category filter*/
        $category = null;
        if($this->property('category')=='current'){
            $category = $this->page[ 'post' ]->categories[0]->id;
        } elseif($this->property('category')=='noFilter'){
            $category = null;
        } else {
            $category = $this->property('category');
        }

        /* Get post page */
        $this->postPage = $this->property('postPage') ? $this->property('postPage') : '404';


        if($this->page[ 'post' ]){
            if($this->page[ 'post' ]->id){
                $currentPostId = $this->page['post']->id;
            }

            $p =  Post::isPublished();
            $p  ->where('id','<',$currentPostId)
                ->orderBy('id','desc');
            if ($category !== null) {
                if (!is_array($category)) $category = [$category];
                $p->whereHas('categories', function($q) use ($category) {
                    $q->whereIn('id', $category);
                });
            }
            $prevPost = $p->first();

            $n =  Post::isPublished();
            $n  ->where('id','>',$currentPostId)
                ->orderBy('id','asc');
            if ($category !== null) {
                if (!is_array($category)) $category = [$category];
                $n->whereHas('categories', function($q) use ($category) {
                    $q->whereIn('id', $category);
                });
            }
            $nextPost = $n->first();

            /* Agregamos el helper de la URL */
            if(count($prevPost)!=0){
                $prevPost->setUrl($this->postPage,$this->controller);
            }
            if(count($nextPost)!=0){
                $nextPost->setUrl($this->postPage,$this->controller);
            }

        }
        $this->next = $nextPost;
        $this->prev = $prevPost;
    }
}
