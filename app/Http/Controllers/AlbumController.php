<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Faixa;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlbumController extends Controller
{
    private $resposta = [
        'message' => "",
        'content' => [] 
    ];

    private $status = 200;

    public function index(){
        $pesquisa_nome = request("nome");
        $limite = request("limite");
        $limite = empty($limite) ? 6 : $limite;

        $query = Album::select('albuns.id', 'albuns.nome', 'albuns.ano', 'albuns.imagem', 'albuns.spotify_link')
            ->selectRaw('COUNT(faixas.id) as quantidade_faixas')
            ->selectRaw('SUM(faixas.duracao) as duracao_total')
            ->leftJoin('album_faixa', 'albuns.id', '=', 'album_faixa.album_id')
            ->leftJoin('faixas', 'album_faixa.faixa_id', '=', 'faixas.id');

        if(!empty($pesquisa_nome))
            $query->where('albuns.nome', 'ilike', '%' . $pesquisa_nome . '%');

        $this->resposta['content'] = $query->orderBy('ano', 'desc')->groupBy('albuns.id')->paginate($limite);

        if(count($this->resposta['content']) == 0){
            $this->resposta['message'] = "Não foi encontrado nenhum álbum";
            $this->status = 404;
        }else
            $this->resposta['message'] = "Lista carregada com sucesso";
        return response()->json($this->resposta,  $this->status);
    }

    public function show($id){
        $album = Album::find($id);
        if(empty($album)){
            $this->resposta['message'] = "Falha ao encontrar Álbum";
            $this->status = 404;
        }else
            $this->resposta['content'] = $album;
        return response()->json($this->resposta, $this->status);
    }

    public function faixas($id){
        $this->resposta['content'] = DB::table('faixas')
            ->select('faixas.*', DB::raw('(SELECT array_agg(album_id) FROM album_faixa WHERE faixa_id = faixas.id) as outros_albuns'))
            ->join('album_faixa', 'faixas.id', '=', 'album_faixa.faixa_id')
            ->join('albuns', 'album_faixa.album_id', '=', 'albuns.id')
            ->where('albuns.id', $id)
            ->get();
        if(count($this->resposta['content']) == 0){
            $this->resposta['message'] = "Não foram encontradas faixas";
            $this->status = 404;
        }else
            $this->resposta['message'] = "Lista carregada com sucesso";
        return response()->json($this->resposta,  $this->status);
    }

    public function store(Request $request, Album $album){
        $valid = $request->validate([
            'nome' => 'required|unique:App\Models\Album,nome|max:100|string',
            'ano' => 'required|digits:4|integer',
            'imagem' => 'nullable|string',
            'spotify_link' => 'nullable|string',
        ]);
        $album->fill($valid);
        try {
            if($album->save())
                $this->resposta['message'] = "Álbum salvo com sucesso!";
            } catch (QueryException $e){
                report($e);
                $this->resposta['errors']['query'][] = "Houve um problema na requisição.";
                $this->status = 500;
            }

        return response()->json($this->resposta, $this->status);        
    }

    public function update(Request $request, $id){
        $valid = $request->validate([
            'nome' => [
                'required', Rule::unique('albuns')->ignore($id), 'max:100', 'string'
            ],
            'ano' => 'required|digits:4|integer',
            'imagem' => 'nullable|string',
            'spotify_link' => 'nullable|string',
        ]);

        $album = Album::find($id);
        if(empty($album)){
            $this->resposta['message'] = "Álbum não encontrado!";
            $this->status = 404;
        }
        else{
            $album->fill($valid);
            try{
                $album->save();
                $this->resposta['message'] = "Álbum salvo com sucesso!";
            } catch (QueryException $e){
                report($e);
                $this->resposta['errors']['query'][] = "Houve um problema na requisição.";
                $this->status = 500;
            }
        }

        return response()->json($this->resposta, $this->status);
    }

    public function destroy($id){
        $album = Album::find($id);
        if($album){
            $album->delete();
            $this->resposta['message'] = "Álbum removido com sucesso!";
        }else{
            $this->resposta['message'] = "Erro ao remover álbum!";
            $this->status = 500;
        }
            
        return response()->json($this->resposta, $this->status);
    }

    public function indexSimplify(){
        $this->resposta['content'] = Album::select('id','nome')->get()->sortBy('id')->values();
        if(count($this->resposta['content']) == 0){
            $this->resposta['message'] = "Não foi encontrado nenhum álbum";
            $this->status = 404;
        }else
            $this->resposta['message'] = "Lista carregada com sucesso";
        return response()->json($this->resposta,  $this->status);
    }

    public function desvincularFaixa(int $id, int $faixa_id){
        $album = Album::find($id);
        if($album){
            $album->faixas()->detach($faixa_id);
            $this->resposta['message'] = "Faixa removida com sucesso!";
        }else{
            $this->resposta['message'] = "Erro ao remover faixa!";
            $this->status = 500;
        }
            
        return response()->json($this->resposta, $this->status);
    }

    public function atribuirFaixa(int $id, int $faixa_id){
        $album = Album::find($id);
        if($album){
            $album->faixas()->attach($faixa_id);
            $this->resposta['message'] = "Faixa adicionada com sucesso!";
        }else{
            $this->resposta['message'] = "Erro ao adicionar faixa!";
            $this->status = 500;
        }
            
        return response()->json($this->resposta, $this->status);
    }

    public function carregarDoSpotify(Request $request){
        
        $valid = $request->validate([
            'token' => 'required|string',
            'limit' => 'required|max:50|integer',
            'offset' => 'required|integer',
            'artist' => 'required|string',
        ]);
        $url_albuns = "https://api.spotify.com/v1/artists/"
            .$valid['artist']."/albums?include_groups=album&offset="
            .$valid['offset']."&limit="
            .$valid['limit']."&market=BR&locale=pt-BR";
        $curl = curl_init($url_albuns);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $valid['token']));
        $res = curl_exec($curl);
        if($res == false){
            $this->resposta['message'] = "Houve um erro ao solicitar a requisição dos Álbuns";
            $this->resposta['errors'][] = curl_error($curl);
            return response()->json($this->resposta, 500);
        }
        $spotify_albuns = json_decode($res);
        curl_close($curl);
        if(!isset($spotify_albuns->items)){
            $this->resposta['message'] = "Houve um erro ao solicitar a requisição dos Álbuns";
            $this->resposta['errors'][] = $spotify_albuns;
            return response()->json($this->resposta, 500);
        }
        

        foreach ($spotify_albuns->items as $al) {
            $album = new Album();
            $album->nome = $al->name;
            $album->ano = date('Y', strtotime($al->release_date));
            $album->imagem = $al->images[1]->url;
            $album->spotify_link = $al->external_urls->spotify;
            
            $url_faixas = "https://api.spotify.com/v1/albums/".$al->id."/tracks";
            $curl = curl_init($url_faixas);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $valid['token']));
            $res = curl_exec($curl);
            if($res == false){
                $this->resposta['message'] = "Houve um erro ao solicitar a requisição das Faixas";
                $this->resposta['errors'][] = curl_error($curl);
                return response()->json($this->resposta, 500);
            }
            $faixas = json_decode($res);
            curl_close($curl);
            if(!isset($faixas->items)){
                $this->resposta['message'] = "Houve um erro ao solicitar a requisição das Faixas";
                $this->resposta['errors'][] = $faixas;
                return response()->json($this->resposta, 500);
            }
            try {
                if($album->save())
                    $this->resposta['ultimoAlbum'] = $album->nome;
            } catch ( UniqueConstraintViolationException $e){
                $album = Album::where('nome', $album->nome)->first();
            } catch (QueryException $e){
                report($e);
                $this->resposta['errors']['query'][] = $e;
                return response()->json($this->resposta,500);
            }
            foreach ($faixas->items as $fx) {
                $faixa = new Faixa();
                $faixa->duracao = $fx->duration_ms;
                $faixa->nome = $fx->name;
                $faixa->spotify_link = $fx->external_urls->spotify;
                try {
                    if($faixa->save())
                        $this->resposta['ultimaFaixaNoAlbum'] = $faixa->nome. " - ". $album->nome;
                } catch ( UniqueConstraintViolationException $e){
                    $faixa = Faixa::where('nome', $faixa->nome)->first();
                } catch (QueryException $e){
                    report($e);
                    $this->resposta['errors']['query'][] = $e;
                    return response()->json($this->resposta,500);
                }
                try{
                    $album->faixas()->attach($faixa->id);
                }catch ( UniqueConstraintViolationException $e){
                    
                }catch (QueryException $e){
                    report($e);
                    $this->resposta['errors']['query'][] = $e;
                    return response()->json($this->resposta,500);
                }
               
            }
        }

        $this->resposta['message'] = "Conteúdo carregado com sucesso";
        return response()->json($this->resposta, $this->status);
    }

}
