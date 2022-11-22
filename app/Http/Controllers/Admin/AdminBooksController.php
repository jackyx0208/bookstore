<?php

namespace App\Http\Controllers\Admin;

use App\Book;
use App\Http\Requests\BooksCreateRequest;
use App\Http\Requests\BooksUpdateRequest;
use App\Image;
use App\PdfFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as Photo;
use Sburina\Whmcs\Facades\Whmcs;

class AdminBooksController extends AdminBaseController
{
    public function index()
    {
        $books = Book::with('category', 'author', 'image', 'pdf_file')
            ->orderBy('id', 'DESC')
            ->get();
        return view('admin.books.index', compact('books'));
    }
    public function create()
    {
        return view('admin.books.create');
    }
    public function store(BooksCreateRequest $request)
    {
        $input = $request->all();

        

        $result = \Whmcs::AddProduct([
            // 'name' => $request->name,
            // 'gid' => 4,
            'type' => 'other',
            'gid' => '1',
            'name' => 'Sample Product111111',
            'welcomeemail' => '5',
            'paytype' => 'recurring',
            'pricing' => array(1 => array('monthly' => 1.00, 'msetupfee' => 1.99, 'quarterly' => 2.00, 'qsetupfee' => 1.99, 'semiannually' => 3.00, 'ssetupfee' => 1.99, 'annually' => 4.00, 'asetupfee' => 1.99, 'biennially' => 5.00, 'bsetupfee' => 1.99, 'triennially' => 6.00, 'tsetupfee' => 1.99)),
            'recommendations' => array(array('id' => 1, 'order' => 0), array('id' => 2, 'order' => 1)),
        ]);
        if($result["result"] != "success")
            return redirect('/admin/books')
            ->with('success_message', 'Book creation failed');
        $result = \Whmcs::GetProducts([
        ]);
        $input['id'] = count($result['products']['product']) - 1;

        $count_discount = (($request->init_price * $request->discount_rate)/100);
        $final_price  = $request->init_price - $count_discount;
        $input['price'] = $final_price;

        if($file = $request->file('image_id'))
        {
            $name = time().$file->getClientOriginalName();

            $image_resize = Photo::make($file->getRealPath());
            $image_resize->resize(340,380);
            $image_resize->save(public_path('assets/img/' .$name));

            $image = Image::create(['file'=>$name]);
            $input['image_id'] = $image->id;
        }
        if($pdf_file = $request->file('pdf_id'))
        {
            $pdf_name = $pdf_file->getClientOriginalName();
            $pdf_name = 'assets/pdf/'.$pdf_name;
            $pdf = PdfFile::create(['pdf_file'=>$pdf_name]);
            $input['pdf_id'] = $pdf->id;

            $sourceFilePath=$pdf_file->getRealPath();
            $destinationPath=public_path()."/$pdf_name";
            $success = \File::copy($sourceFilePath,$destinationPath);   
        }

        $create_books = Book::create($input);
        return redirect('/admin/books')
            ->with('success_message', 'Book created successfully');

    }

    public function edit($id)
    {
        $book = Book::findOrFail($id);
        return view('admin.books.edit', compact('book'));

    }
    public function update(BooksUpdateRequest $request, $id)
    {
        $input = $request->all();

        $count_discount = (($request->init_price * $request->discount_rate)/100);
        $final_price  = $request->init_price - $count_discount;
        $input['price'] = $final_price;

        if($file = $request->file('image_id'))
        {
            $name = time().$file->getClientOriginalName();

            $image_resize = Photo::make($file->getRealPath());
            $image_resize->resize(340,380);
            $image_resize->save(public_path('assets/img/' .$name));

            $image = Image::create(['file'=>$name]);
            $input['image_id'] = $image->id;
        }

        if($pdf_file = $request->file('pdf_id'))
        {
            $pdf_name = $pdf_file->getClientOriginalName();
            $pdf_name = 'assets/pdf/'.$pdf_name;
            $pdf = PdfFile::create(['pdf_file'=>$pdf_name]);
            $input['pdf_id'] = $pdf->id;

            $sourceFilePath=$pdf_file->getRealPath();
            $destinationPath=public_path()."/$pdf_name";
            $success = \File::copy($sourceFilePath,$destinationPath); 
        }

        $book = Book::findOrFail($id);
        $book->update($input);
        return redirect('/admin/books')
            ->with('success_message', 'Book updated successfully');

    }

    public function destroy($id)
    {
        $book= Book::findOrFail($id);
        $book->delete();
        return redirect()->back()->with('alert_message', 'Book move to trash');
    }

    public function restore($id)
    {
        $trash = Book::onlyTrashed()->findOrFail($id);
        $trash->restore();
        return redirect()->back()
            ->with('success_message', 'Book successfully restore from trash');
    }

    public function forceDelete($id)
    {
        $trash_book = Book::onlyTrashed()->findOrfail($id);
        if(!is_null($trash_book->image_id))
        {
            unlink(public_path().'/assets/img/'.$trash_book->image->file);
        }
        $trash_book->image->delete();
        $trash_book->forceDelete();
        return redirect()->back()
            ->with('alert_message', 'Book deleted permanently');
    }

    public function trashBooks()
    {
        $books = Book::onlyTrashed()->orderBy('id', 'DESC')->get();
        return view('admin.books.trash-books', compact('books'));
    }

    public function discountBooks()
    {
        $discount_books = "All books with discount";
        $books = Book::with('author', 'category')
            ->orderBy('discount_rate', 'DESC')
            ->where('discount_rate', '>', 0)->get();

        return view('admin.books.index', compact('books', 'discount_books'));
    }


}
