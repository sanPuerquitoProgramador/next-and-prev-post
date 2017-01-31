<?php namespace PolloZen\NextPrevPost\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Blog\Models\Post;

class NextPrev extends ComponentBase
{
    public $next;
    public $prev;
    public function componentDetails()
    {
        return [
            'name'        => 'NextPrev Component',
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
        $categories =  Category::orderBy('name')->lists('name','id');
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
        $category = $this->property('category') ? $this->property('category') : null;

        /* Get post page */
        $this->postPage = $this->property('postPage') ? $this->property('postPage') : '404';

        /*Get the category filter*/
        $category = $this->property('category') ? $this->property('category') : null;


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
            $prevPost->setUrl($this->postPage,$this->controller);
            $nextPost->setUrl($this->postPage,$this->controller);

        }
        $this->next = $nextPost;
        $this->prev = $prevPost;
    }
}
