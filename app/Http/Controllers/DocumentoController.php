namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    public function index()
    {
        $archivos = Storage::files('public/archivos');
        return view('archivos.index', compact('archivos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240',
        ]);

        $request->file('archivo')->store('public/archivos');

        return redirect()->route('archivos.index')->with('success', 'Archivo subido correctamente');
    }

    public function destroy($archivo)
    {
        Storage::delete('public/archivos/' . $archivo);
        return back()->with('success', 'Archivo eliminado');
    }
}
