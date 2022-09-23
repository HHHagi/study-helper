<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreTargetRequest;
use App\Http\Requests\UpdateTargetRequest;
use App\Http\Requests\StoreUpdateMemoRequest;

use App\Models\Target;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Idea;
use App\Models\PrivateCategory;
use App\Models\PublicCategory;
use App\Models\Book;
use App\Models\Task;
use App\Models\BookExplanation;
use App\Models\TaskExplanation;

class TargetController extends Controller
{
    // ログインしてなければログイン画面へ飛ばす
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Auth::user()->id;
        $targets = Target::where('user_id', $user_id)->orderBy('id', 'desc')->get();
        $ideas = Idea::where('user_id', $user_id)->orderBy('id', 'desc')->get();
        $private_categories = PrivateCategory::where('user_id', $user_id)->get();
        $public_categories = PublicCategory::all();
        return view('contents_views.targets', compact('targets', 'ideas', 'private_categories', 'public_categories'));
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
    public function store(StoreTargetRequest $request)
    {
        $targets = new Target;
        $form  = $request->all();
        $targets->fill($form);
        $targets->user_id = Auth::user()->id;
        $targets->save();
        return redirect('/targets');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $user_id = Auth::user()->id;
        $target = Target::find($id);
        $private_categories = PrivateCategory::all();
        $ideas = Idea::where('user_id', $user_id)->orderBy('id', 'desc')->get();

        // $books = Book::with('book_explanations')->whereHas('book_explanations', function ($query) {
        //     $query->whereExists(function ($query) {
        //         return $query;
        //     });
        // })->get();

        // $tasks = Task::with('task_explanations')->whereHas('task_explanations', function ($query) {
        //     $query->whereExists(function ($query) {
        //         return $query;
        //     });
        // })->get();

        // $books = Book::where('target_id', $id)->whereDoesntHave('book_explanations', function ($query) {
        //     $query->whereExists(function ($query) {
        //         return $query;
        //     });
        // })->get();

        // $tasks = Task::with('task_explanations')->whereDoesntHave('task_explanations', function ($query) {
        //     $query->whereExists(function ($query) {
        //         return $query;
        //     });
        // })->get();
        $books = Book::where('target_id', $id)->orderBy('id', 'desc')->get();
        $tasks = Task::where('target_id', $id)->orderBy('id', 'desc')->get();
        // $request->private_category_idを選択した場合 
        if ($request->private_category_id) {
            $books = Book::where('target_id', $id)->where('private_category_id', $request->private_category_id)->orderBy('id', 'desc')->get();
            $tasks = Task::where('target_id', $id)->where('private_category_id', $request->private_category_id)->orderBy('id', 'desc')->get();
            // $request->private_category_idと$request->priorityの選択の場合 
            if ($request->priority) {
                $books = Book::where('target_id', $id)->where('private_category_id', $request->private_category_id)->where('priority', $request->priority)->orderBy('id', 'desc')->get();
                $tasks = Task::where('target_id', $id)->where('private_category_id', $request->private_category_id)->where('priority', $request->priority)->orderBy('id', 'desc')->get();
                // $request->private_category_idと$request->priorityとis_doneの選択の場合
                if ($request->is_done == 1) {
                    $books = Book::where('target_id', $id)->where('private_category_id', $request->private_category_id)->where('priority', $request->priority)
                        ->whereHas('book_explanations', function ($query) {
                            $query->whereExists(function ($query) {
                                return $query;
                            });
                        })->orderBy('id', 'desc')->get();
                    $tasks = Task::where('target_id', $id)->where('private_category_id', $request->private_category_id)->where('priority', $request->priority)
                        ->whereHas('task_explanations', function ($query) {
                            $query->whereExists(function ($query) {
                                return $query;
                            });
                        })->orderBy('id', 'desc')->get();
                }
                if ($request->is_done == 2) {
                    $books = Book::where('target_id', $id)->where('private_category_id', $request->private_category_id)->where('priority', $request->priority)
                        ->whereDoesntHave('book_explanations', function ($query) {
                            $query->whereExists(function ($query) {
                                return $query;
                            });
                        })->orderBy('id', 'desc')->get();
                    $tasks = Task::where('target_id', $id)->where('private_category_id', $request->private_category_id)->where('priority', $request->priority)
                        ->whereDoesntHave('task_explanations', function ($query) {
                            $query->whereExists(function ($query) {
                                return $query;
                            });
                        })->orderBy('id', 'desc')->get();
                }
            }
            // $request->private_category_idとis_doneの選択の場合
            if ($request->is_done == 1) {
                $books = Book::where('target_id', $id)->where('private_category_id', $request->private_category_id)
                    ->whereHas('book_explanations', function ($query) {
                        $query->whereExists(function ($query) {
                            return $query;
                        });
                    })->orderBy('id', 'desc')->get();
                $tasks = Task::where('target_id', $id)->where('private_category_id', $request->private_category_id)
                    ->whereHas('task_explanations', function ($query) {
                        $query->whereExists(function ($query) {
                            return $query;
                        });
                    })->orderBy('id', 'desc')->get();
            }
            if ($request->is_done == 2) {
                $books = Book::where('target_id', $id)->where('private_category_id', $request->private_category_id)
                    ->whereDoesntHave('book_explanations', function ($query) {
                        $query->whereExists(function ($query) {
                            return $query;
                        });
                    })->orderBy('id', 'desc')->get();
                $tasks = Task::where('target_id', $id)->where('private_category_id', $request->private_category_id)
                    ->whereDoesntHave('task_explanations', function ($query) {
                        $query->whereExists(function ($query) {
                            return $query;
                        });
                    })->orderBy('id', 'desc')->get();
            }
        }
        if (!$request->private_category_id && $request->priority) {
            // $request->priorityのみの場合
            $books = Book::where('target_id', $id)->where('priority', $request->priority)->orderBy('id', 'desc')->get();
            $tasks = Task::where('target_id', $id)->where('priority', $request->priority)->orderBy('id', 'desc')->get();
            // $request->prirityとis_doneの場合
            if ($request->is_done == 1) {
                $books = Book::where('target_id', $id)->where('priority', $request->priority)
                    ->whereHas('book_explanations', function ($query) {
                        $query->whereExists(function ($query) {
                            return $query;
                        });
                    })->orderBy('id', 'desc')->get();
                $tasks = Task::where('target_id', $id)->where('priority', $request->priority)
                    ->whereHas('task_explanations', function ($query) {
                        $query->whereExists(function ($query) {
                            return $query;
                        });
                    })->orderBy('id', 'desc')->get();
            }
            if ($request->is_done == 2) {
                $books = Book::where('target_id', $id)->where('priority', $request->priority)
                    ->whereDoesntHave('book_explanations', function ($query) {
                        $query->whereExists(function ($query) {
                            return $query;
                        });
                    })->orderBy('id', 'desc')->get();
                $tasks = Task::where('target_id', $id)->where('priority', $request->priority)
                    ->whereDoesntHave('task_explanations', function ($query) {
                        $query->whereExists(function ($query) {
                            return $query;
                        });
                    })->orderBy('id', 'desc')->get();
            }
        }

        if (!$request->private_category_id && !$request->priority) {
            // is_doneのみの場合
            if($request->is_done == 1){
                $books = Book::where('target_id', $id)
                ->whereHas('book_explanations', function ($query) {
                    $query->whereExists(function ($query) {
                        return $query;
                    });
                })->orderBy('id', 'desc')->get();
            $tasks = Task::where('target_id', $id)
                ->whereHas('task_explanations', function ($query) {
                    $query->whereExists(function ($query) {
                        return $query;
                    });
                })->orderBy('id', 'desc')->get();
            }
            if($request->is_done == 2){
                $books = Book::where('target_id', $id)
                ->whereDoesntHave('book_explanations', function ($query) {
                    $query->whereExists(function ($query) {
                        return $query;
                    });
                })->orderBy('id', 'desc')->get();
            $tasks = Task::where('target_id', $id)
                ->whereDoesntHave('task_explanations', function ($query) {
                    $query->whereExists(function ($query) {
                        return $query;
                    });
                })->orderBy('id', 'desc')->get();
            }
        }


        // if ($request->private_category_id && $request->priority && $request->is_done) {
        //     $books = Book::where('target_id', $id)->where('private_category_id', $request->private_category_id)->where('priority', $request->priority)
        //     ->whereHas('book_explanations', function ($query) {
        //         $query->whereExists(function ($query) {
        //             return $query;
        //         });
        //     })->orderBy('id', 'desc')->get();
        //     $tasks = Task::where('target_id', $id)->where('private_category_id', $request->private_category_id)->where('priority', $request->priority)
        //     ->whereHas('task_explanations', function ($query) {
        //         $query->whereExists(function ($query) {
        //             return $query;
        //         });
        //     })->orderBy('id', 'desc')->get();
        // }

        // if ($request->private_category_id) {
        //     $select_private_category_id = $request->private_category_id;
        //     if ($request->is_done) {
        //         $select_is_done = $request->is_done;
        //         if ($request->priority) {
        //             $select_priority = $request->priority;
        //             $books = Book::where('target_id', $id)->where('private_category_id', $select_private_category_id)->where('is_done', $select_is_done)->where('priority', $select_priority)->orderBy('id', 'desc')->get();
        //             $tasks = Task::where('target_id', $id)->where('private_category_id', $select_private_category_id)->where('is_done', $select_is_done)->where('priority', $select_priority)->orderBy('id', 'desc')->get();
        //         }
        //         $books = Book::where('target_id', $id)->where('private_category_id', $select_private_category_id)->where('is_done', $select_is_done)->orderBy('id', 'desc')->get();
        //         $tasks = Task::where('target_id', $id)->where('private_category_id', $select_private_category_id)->where('is_done', $select_is_done)->orderBy('id', 'desc')->get();
        //     }
        //     $books = Book::where('target_id', $id)->where('private_category_id', $select_private_category_id)->orderBy('id', 'desc')->get();
        //     $tasks = Task::where('target_id', $id)->where('private_category_id', $select_private_category_id)->orderBy('id', 'desc')->get();
        // } elseif ($request->is_done) {
        //     $select_is_done = $request->is_done;
        //     if ($request->priority) {
        //         $select_priority = $request->priority;
        //         $books = Book::where('target_id', $id)->where('is_done', $select_is_done)->where('priority', $select_priority)->orderBy('id', 'desc')->get();
        //         $tasks = Task::where('target_id', $id)->where('is_done', $select_is_done)->where('priority', $select_priority)->orderBy('id', 'desc')->get();
        //     }
        //     $books = Book::where('target_id', $id)->where('is_done', $select_is_done)->orderBy('id', 'desc')->get();
        //     $tasks = Task::where('target_id', $id)->where('is_done', $select_is_done)->orderBy('id', 'desc')->get();
        // } elseif ($request->priority) {
        //     $select_priority = $request->priority;
        //     $books = Book::where('target_id', $id)->where('priority', $select_priority)->orderBy('id', 'desc')->get();
        //     $tasks = Task::where('target_id', $id)->where('priority', $select_priority)->orderBy('id', 'desc')->get();
        // } else {
        //     $books = Book::where('target_id', $id)->orderBy('id', 'desc')->get();
        //     $tasks = Task::where('target_id', $id)->orderBy('id', 'desc')->get();
        // }
        $book_explanations = BookExplanation::where('target_id', $id)->orderBy('id', 'desc')->get();
        $task_explanations = TaskExplanation::where('target_id', $id)->orderBy('id', 'desc')->get();
        return view('contents_views.target_edit', compact('target', 'private_categories', 'ideas', 'books', 'tasks', 'book_explanations', 'task_explanations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTargetRequest $request, $id)
    {
        $target = Target::find($id);
        $form  = $request->all();
        $target->update($form);
        return redirect('/targets');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $target = Target::find($id);
        $target->delete();
        return redirect('/targets');
    }
}
