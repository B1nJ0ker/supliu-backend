<?php

namespace App\Http\Controllers;

use App\Models\Faixa;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaixaController extends Controller
{
    private $resposta = [
        'message' => "",
        'content' => [] 
    ];

    private $status = 200;

    public function index(){
        $pesquisa_nome = request("nome", "");
        $limite = request("limite", 6);

        $query = Faixa::select('id','nome','duracao','spotify_link');

        if(!empty($pesquisa_nome))
            $query->where('nome', 'ilike', '%' . $pesquisa_nome . '%');
        
        $this->resposta['content'] = $query->orderBy('nome', 'asc')->paginate($limite);
        if(count($this->resposta['content']) == 0){
            $this->resposta['message'] = "Não foi encontrada nenhuma faixa";
            $this->status = 404;
        }else
            $this->resposta['message'] = "Lista carregada com sucesso";
        return response()->json($this->resposta, $this->status);
    }

    public function show($id){
        $faixa = Faixa::find($id);
        if(empty($faixa)){
            $this->resposta['message'] = "Nenhuma faixa encontrada";
            $this->status = 404;
        }else
            $this->resposta['content'] = $faixa;
        return response()->json($this->resposta, $this->status);
    }

    public function store(Request $request, Faixa $faixa){
        $valid = $request->validate([
            'nome' => 'required|unique:App\Models\Faixa,nome|max:100|string',
            'albuns' => 'required|min:1|array',
            'albuns.*' => 'exists:App\Models\Album,id',
            'duracao' => 'required|integer',
            'spotify_link' => 'nullable|string',
        ]);
        $faixa->fill($valid);
        try {
            if($faixa->save()){
                $this->atribuirAlbuns($valid['albuns'], $faixa);
                $this->resposta['message'] = "Faixa salva com sucesso!";}
            } catch (QueryException $e){
                report($e);
                $this->resposta['errors']['query'][] = "Houve um problema na requisição.";
                $this->status = 500;
            }

        if(!empty($this->resposta['errors'])){
            $this->resposta['message'] = "Falha ao salvar faixa!";
        }
        return response()->json($this->resposta, $this->status);
        
    }

    public function update(Request $request, $id){
        $valid = $request->validate([
            'nome' => [
                'required', Rule::unique('faixas')->ignore($id), 'max:100', 'string'
            ],
            'albuns' => 'required|min:1|array',
            'albuns.*' => 'exists:App\Models\Album,id',
            'duracao' => 'required|integer',
            'spotify_link' => 'string',
        ]);

        $faixa = Faixa::find($id);
        if(empty($faixa)){
            $this->resposta['message'] = "Faixa não encontrada!";
            $this->status = 404;
        }
        else{
            $faixa->fill($valid);
            try{
                $faixa->save();
                $this->desvincularAlbuns($valid['albuns'], $faixa);
                $this->atribuirAlbuns($valid['albuns'], $faixa);
                $this->resposta['message'] = "Faixa salva com sucesso!";
            } catch (QueryException $e){
                report($e);
                $this->resposta['errors']['query'][] = "Houve um problema na requisição.";
                $this->status = 500;
            }
        }

        if(!empty($this->resposta['errors'])){
            $this->resposta['message'] = "Falha ao salvar álbum!";
        }

        return response()->json($this->resposta, $this->status);

    }

    public function destroy($id){
        $faixa = Faixa::find($id);
        if($faixa){
            $faixa->delete();
            $this->resposta['message'] = "Faixa removida com sucesso!";
        }else{
            $this->resposta['message'] = "Erro ao remover faixa!";
            $this->status = 404;
        }
            
        return response()->json($this->resposta, $this->status);
    }

    private function atribuirAlbuns(array $albuns,Faixa $faixa){
        foreach (array_diff($albuns, array_column($faixa->albuns()->get()->toArray(), 'id')) as $id) {
            $faixa->albuns()->attach($id);
        }
    }

    private function desvincularAlbuns(array $albuns,Faixa $faixa){
        foreach (array_diff(array_column($faixa->albuns()->get()->toArray(), 'id'), $albuns) as $id) {
            $faixa->albuns()->detach($id);
        }
    }

    public function albuns($id){
        $albuns = Faixa::find($id)->albuns;
        if(empty($albuns)){
            $this->resposta['message'] = "Falha ao encontrar Álbuns";
            $this->status = 404;
        }else
            $this->resposta['content'] = $albuns;
        return response()->json($this->resposta, $this->status);
    }

    public function indexSimplify(){
        $this->resposta['content'] = Faixa::select('id','nome')->get()->sortBy('nome')->values();
        if(count($this->resposta['content']) == 0){
            $this->resposta['message'] = "Não foi encontrado nenhum álbum";
            $this->status = 404;
        }else
            $this->resposta['message'] = "Lista carregada com sucesso";
        return response()->json($this->resposta,  $this->status);
    }

}
