<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Repositories\ArticleRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Response;
use App\Models\Category;
use App\Traits\UploadFileTrait;
use Illuminate\Support\Arr;

class ArticleController extends AppBaseController
{
    use UploadFileTrait;
    
    /** @var  ArticleRepository */
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepo)
    {
        $this->articleRepository = $articleRepo;
    }

    /**
     * Display a listing of the Article.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $perPage = (env('PAGINATION_ROWS')) ? env('PAGINATION_ROWS') : 10;
        $articles = $this->articleRepository->paginate($perPage);

        return view('articles.index')
            ->with('articles', $articles);
    }

    /**
     * Show the form for creating a new Article.
     *
     * @return Response
     */
    public function create()
    {
        $categories = Category::pluck('name','id')->all();    
        return view('articles.create', compact('categories'));
    }

    /**
     * Store a newly created Article in storage.
     *
     * @param CreateArticleRequest $request
     *
     * @return Response
     */
    public function store(CreateArticleRequest $request)
    {
        $input = $request->all();
        $article = $this->articleRepository->create($input);
        $article->image = $this->articleimage($article, $request);
        $articleUpdate = $this->articleRepository->update(['image' => $article->image], $article->id);

        Flash::success('Article saved successfully.');

        return redirect(route('articles.index'));
    }

    /**
     * Display the specified Article.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $article = $this->articleRepository->find($id);

        if (empty($article)) {
            Flash::error('Article not found');

            return redirect(route('articles.index'));
        }

        return view('articles.show')->with('article', $article);
    }

    /**
     * Show the form for editing the specified Article.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $article = $this->articleRepository->find($id);

        if (empty($article)) {
            Flash::error('Article not found');

            return redirect(route('articles.index'));
        }
        $categories = Category::pluck('name','id')->all();

        return view('articles.edit')->with(['article' => $article, 'categories' => $categories]);
    }

    /**
     * Update the specified Article in storage.
     *
     * @param int $id
     * @param UpdateArticleRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateArticleRequest $request)
    {
        $article = $this->articleRepository->find($id);

        if (empty($article)) {
            Flash::error('Article not found');

            return redirect(route('articles.index'));
        }
        $input = Arr::except(request()->all(), ['image']);
        $article = $this->articleRepository->update($input, $id);
        if($request->file('image') != ''){
            $path = public_path().'/articleimage/';
            
            //code for remove old file
            if($article->image != '' && $article->image != null){
                $file_old = $path . '/' . $article->id . '/' . $article->image;
                $thumb_file_old = $path . '/' . $article->id . '/thumb_' . $article->image;
                unlink($file_old);
                unlink($thumb_file_old);
            }
            
            //upload new file
            $article->image = $this->articleimage($article, $request);
            $articleUpdate = $this->articleRepository->update(['image' => $article->image], $article->id);
        }
        Flash::success('Article updated successfully.');

        return redirect(route('articles.index'));
    }

    /**
     * Remove the specified Article from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $article = $this->articleRepository->find($id);

        if (empty($article)) {
            Flash::error('Article not found');

            return redirect(route('articles.index'));
        }

        $this->articleRepository->delete($id);

        Flash::success('Article deleted successfully.');

        return redirect(route('articles.index'));
    }
}
