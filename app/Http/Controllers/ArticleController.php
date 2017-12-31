<?php

namespace App\Http\Controllers;

use App\Article;
use App\Tag;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Article::latest()->with('Tags')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return Article::create([ 
            'text' => $request->article['text'],
            'published' => $request->article['published']  
         ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'article.text' => 'required|min:3'
        ]);
        
        $article = Article::findOrFail($id);
        
        $input['text'] = $request->article['text'];
        $input['published'] = $request->article['published'];

        $article->fill($input)->save();
    }
  
    /**
     * Attach Tag to Article. If Tag doesn´t exist, create it.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function attachNewTag(Request $request)
    {
        $tag = Tag::firstOrCreate(array('name' => $request->tagName));
        $article = Article::with('Tags')->findOrFail($request->articleId);
        
        // attach tag to pivot table only once
        $article->tags()->syncWithoutDetaching([$tag->id]);
    
        return response()->json($article->tags);
    }
    
     /**
     * Remove Tag from Article
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function detachTagByTagId(Request $request)
    {   
        $article = Article::with('Tags')->find($request->articleId);
        $article->tags()->detach([$request->tagId]);
        
        $this->cleanUpTags();
        
        return response()->json($article->tags);
    }
    
     /**
     * @todo: remove
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function test(Request $request)
    {
        $articles = Article::with('tags')->get();
        
        return response($articles, 200);
    }
    
    /**
     * Delete Tags that are not attached to an Article
     */
    public function cleanUpTags()
    {
        $tags = Tag::latest()->withCount('articles')->get();
        
        foreach($tags as $tag){
            if($tag->articles_count === 0){
                $tag->delete();
            }
        }
    }
    
    /**
     * Get List of all Tags
     * @return type
     */
    public function getTags()
    {
        return Tag::latest()->withCount('articles')->get();
    }
    
    /**
     * Filter Article collection with tags
     * @param array $tagIds
     * @return type
     */
    protected function getArticlesFilteredByTagIds(Array $tagIds)
    {
        $articles = Article::whereHas('tags', function($query) use($tagIds){
            $query->whereIn('id', $tagIds);
        })->with('tags')->get();
        
        return $articles;
    }
    
    /**
     * Get filtered list of articles
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filterArticles(Request $request)
    {
        if(isset($request->tagIds) && count($request->tagIds)>0){
            return $this->getArticlesFilteredByTagIds($request->tagIds);
        } else {
            return Article::latest()->with('Tags')->get();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();
        return 204;
    }
}