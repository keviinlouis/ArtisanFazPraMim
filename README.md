# Artisan Faz Pra mim

## TODOS 
- Atrelar relations no store e no update
- Corrigir os Bugs encontrados
- Copiar Classe Request
- Analisar se vale a pena deixar o base files na vendor

## Como Usar

Tenha certeza que seu banco está modelado, de preferencia em ingles, e seu ``.env`` está configurado corretamente <br>

Rode o comando <br>
``composer require louisk/artisan-faz-pra-mim``

Se o seu projeto for Laravel menor que v5.5, registre em ``config/app.php``
````` 
   /*
   * Package Service Providers...
   */
  Louisk\ArtisanFazPraMim\FazPraMimServiceProvider::class,
````` 

Rode o comando para configurar seu projeto, como pastas e quais features você quer, o comando irá gerar o ``config/faz-pra-mim.php``<br>
Caso queira o projeto default, não precisa configurar<br>
``php artisan vendor:publish --tag=faz-pra-mim``

Rode o comando para gerar seu projeto <br>
``php artisan faz-pra-mim``

Pronto! 

## Estrutura Projeto Artisan Faz Pra Mim

- Controller
- Service
- Rules
- Resource
- Observer
- JWT
- Routes
- Generators

#### Controller

##### Objetivo

- Controlar regras de quem está solicitando um serviço
- Controlar o tipo de retorno para quem está solicitando o serviço

##### Diretório
- `app/Http/Controllers/{Guard}`
- Substituir a {guard} pelo tipo de usuário, por exemplo Cliente ou Admin

##### Exemplo de função:

    public function index(Request $request): OficinaResource
    {
        $data = $request->toCollection();

        $data['ativo'] = true;

        $data['plano_ativo'] = true;

        if (auth()->user()->carroAtual) {
            $data['veiculo'] = auth()->user()->carroAtual->modelo->veiculo_id;
            $data['marca'] = auth()->user()->carroAtual->modelo->veiculo->marca_id;
        }

        $this->oficinaService->relationsCount = ['avaliacoes'];

        $model = $this->oficinaService->index($data);

        return new OficinaResource($model);
    }

##### Considerações
- Quando um ``cliente`` solicita uma listagem de `oficinas`, deve-se mostrar apenas as ativas e as que possuem plano ativo, alem de mostrar a quantidade de avaliações que ela tem.
- Se caso o cliente possuir um carro, deve-se procurar oficinas para aquela marca e veiculo.
- Geralmente usa-se a função `auth()` nas controllers e não nas services
- Ao conseguir o resultado, deve-se retornar uma ` Resource` caso seja API, ou uma `View` caso seja web.

#### Service

##### Objetivo

- Servir para qualquer controller (qualquer um poder chamar a service)
- Chamar a validação dos dados independente de quem estiver chamando
- Conter a maioria da logica do sistema
- Retornar a ``Model`` caso não seja o método de listagem (`index`) ou uma `Collection` ou `LengthAwarePaginator` caso seja o método `index`
- Usar sempre o `Eloquent`, em casos de dashboard, utilizar `\DB::table()`

##### Diretório
- `app/Services`

##### Documentação
- Relations: https://laravel.com/docs/5.7/eloquent-relationships#defining-relationships
- Queries: https://laravel.com/docs/5.7/queries
- Query por relation: https://laravel.com/docs/5.7/eloquent-relationships#querying-relations

##### Exemplo de função

     public function store(Collection $data): Anuncio
        {

            if ($data->get('credito') && (!$data->get('parcelas') || $data->get('parcelas') <= 0)) {
                $data->put('parcelas', 1);
            }

            $this->validateWithArray($data->toArray(), AnuncioRules::store());

            if (auth()->user() instanceof Usuario && auth()->user()->oficina->creditos_anuncios <= 0) {
                throw new Exception('Oficina sem creditos para criar anuncios', Response::HTTP_BAD_REQUEST);
            }

            $data['thumbnail_id'] = Servico::find($data['servico_id'])->thumbnail_id;

            if (!$data->get('validade')) {
                $data['validade'] = now()->addDays(15);
            }

            \DB::beginTransaction();

            $model = Anuncio::create($data->all());

            $modelos = $data->get('modelos', []);
            $veiculos = $data->get('veiculos', []);
            $marcas = $data->get('marcas', []);

            if (count($modelos) > 0) {
                $model->modelos()->sync($modelos);
            }
            if (count($veiculos) > 0) {
                $model->veiculos()->sync($veiculos);
            }
            if (count($marcas) > 0) {
                $model->marcas()->sync($marcas);
            }

            \DB::commit();
            return $this->show($model->getKey());
        }

##### Considerações
- Tentar ao máximo não utilizar o método `auth()`
- Sempre colocar `\DB::beginTransaction();` e `\DB::commit();` nas modificações do banco
- Sempre que precisar, criar relações baseadas na model
- Sempre utilizar as validações `$this->validateWithArray($data->toArray(), AnuncioRules::store());`
- Sempre validar mudança de status das models
- Se preciso, criar uma função para Validar se o usuário pode alterar uma model (exceção para o uso do método `auth()` )

#### Rules (Validations)

##### Objetivo

- Ter metodos estáticos para serem usados em qualquer lugar do código (Geralmente em services e em outros casos em controllers)
- Conter as regras para criação e edição de todas as `Models`

##### Diretório
- `app/Validators`

##### Documentação
- Validações disponiveis: https://laravel.com/docs/5.7/validation#available-validation-rules
- Validações customizadas: https://laravel.com/docs/5.7/validation#custom-validation-rules

##### Exemplo de função

     public static function store(): array
     {
         return [
             'titulo' => 'required|min:3|string',
             'descricao' => 'required|min:10|string',
             'valor_original' => 'required|numeric|greater_than_field:valor_promocional',
             'valor_promocional' => 'required|numeric|lower_than_field:valor_original',
             'mao_obra' => 'required|numeric',
             'peca_original' => 'nullable|numeric',
             'dinheiro' => 'required|boolean',
             'debito' => 'required|boolean',
             'credito' => 'required|boolean',
             'parcelas' => 'nullable|required_if:credito,true|min:1',
             'validade' => 'nullable|date_format:"Y-m-d"|before:"'.now()->addDays(16)->toDateString().'"',
             'servico_id' => 'required|exists:servicos,id',
             'oficina_id' => 'required|exists:oficinas,id',
             'marcas' => 'array',
             'marcas.*' => 'exists:marcas,id',
             'veiculos' => 'array',
             'veiculos.*' => 'exists:veiculos,id',
             'modelos' => 'array',
             'modelos.*' => 'exists:modelos,id',
             'raio' => 'required|integer|min:1',
         ];
     }

##### Considerações
- Tentar ao máximo validar todos os tipos de campos
- Em campos que são array, validar os campos, por exemplo `'modelos' => 'array'` e `'modelos.*' => 'exists:modelos,id'`
- Se preciso, mandar parametros para as rules, para verificações especificas
- Sempre retornar um array
- Para criar validações customizadas, há duas formas, ``Validator::extend()`` ou criação de uma classe `Rule em `app/Rules`
- Quando utilizar o ``Validator::extend()``, fazer um provider e coloque todos lá, não se esqueça de registar a provider `config/app.php`

#### Observers

##### Objetivo

- Gerenciar as notificações
- Gerenciar mudança de status
- Rotinas obrigatorias para os métodos

##### Diretório
- `app/Observers`

##### Documentação
- Observers: https://laravel.com/docs/5.7/eloquent#observers

##### Exemplo de função

    public function updated(Anuncio $anuncio)
        {
            if ($anuncio->getOriginal('status') !== $anuncio->status) {
                switch ($anuncio->status) {
                    case Anuncio::EM_AVALIACAO:

                        break;
                    case Anuncio::INATIVO:

                        break;
                    case Anuncio::EXPIRADO:
                        $this->notificaOficinaAnuncioExpirado($anuncio);
                        break;
                    case Anuncio::ATIVO:
                        if (auth()->user() instanceof Administrador) {
                            $this->notificaOficinaAnuncioAprovado($anuncio);
                        }
                        break;
                    case Anuncio::REPROVADO:
                        $anuncio->oficina->adicionarCreditos();
                        $this->notificaOficinaAnuncioReprovado($anuncio);
                        break;
                    case Anuncio::REVISAO:
                        $this->notificaOficinaAnuncioRevisao($anuncio);
                        break;
                }
            }
        }

##### Considerações
- Sempre verificar se o campo original (antigo) é diferente do atual (atualizado)
- Mandar notificações
- As funções disponiveis são: retrieved, creating, created, updating, updated, saving, saved,  deleting, deleted, restoring, restored
- Se possivel, crie um provider para registrar os observers e registre em
- Para registrar, use ` User::observe(UserObserver::class);` em um provider `config/app.php`#### Observers

#### Resource

##### Objetivo

- Montar response para os Clients
- Gerenciar tipos de retornos

##### Diretório
- `app/Http/Resources`

##### Documentação
- Resource API: https://laravel.com/docs/5.7/eloquent-resources

##### Exemplo de função

    public function toResource($resource)
    {
        $data = [
            'id' => $resource->getKey(),
            'status' => (int) !$resource->trashed(),
            'nome' => $resource->nome,
            'tipo' => $resource->tipo,
            'meses' => $resource->meses,
            'quilometros' => $resource->quilometros,
            'quilometros_aviso' => $resource->quilometros_aviso,
            'meses_aviso' => $resource->meses_aviso,
            'thumbnail' => new ThumbnailResource($resource->thumbnail),
            'aviso_normal' => $resource->aviso_normal,
            'aviso_manutencao' => $resource->aviso_manutencao,
        ];
        if (request()->has('especialidade')) {
            $data['especialidade'] = new EspecialidadeResource($resource->especialidade);
        }

        return $data;
    }

    public function toCollection($collection)
    {
        if(!request()->has('groupEspecialidade')){
            return parent::toCollection($collection);
        }else{
            $especialidades = [];
            foreach($collection as $value){
                if(!isset($especialidades[$value->especialidade->id])){
                    $especialidades[$value->especialidade->id]['nome'] = $value->especialidade->nome;
                }
                $especialidades[$value->especialidade->id]['servicos'][] = $this->toItemOfCollection($value);

            }
            return $especialidades;
        }
    }

##### Considerações
- Sempre cuidar para não fazer consultas no banco de dados em Resources
- Manter sempre um padrão nas respostas
- Ter cuidado ao mudar a resource, pois há possibilidade de gerar exception para os clients sides
- Se basear nas telas do design

### JWT

##### Objetivo

- Gerenciar o token pelo guard
- Gerenciar o refresh do token

##### Diretório
- `app/Http/Middleware/CheckToken.php`
- Caso não houver este arquivo, copiar de https://github.com/keviinlouis/ProjetoPadraoLaravel/blob/master/app/Http/Middleware/CheckToken.php

##### Documentação
- Pacote: https://github.com/tymondesigns/jwt-auth

##### Descrição
- Para autenticação foi utilizado JWT, onde ao fazer login um token é retornado e esse token precisa ser passado via header Authorization do tipo Bearer
- Exemplo: `Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNTE2Mjc5MTcyLCJleHAiOjE1MTYyODI3NzIsIm5iZiI6MTUxNjI3OTE3MiwianRpIjoib2h0a0VxVUtIdkkzakxMdSIsInN1YiI6MSwicHJ2IjoiZjkzMDdlYjVmMjljNzJhOTBkYmFhZWYwZTI2ZjAyNjJlZGU4NmY1NSJ9.cOjyx6-x3_KHwGgfSh9bSX0g90hNOcyFGB4sMBoMRCI`

- Cada token gerado se expira no tempo registrado no `.env`, logo quando ele for expirado, um parametro new_token no header do response será retornado, substituindo o token antigo.
- Para isso, cada response deverá ser interceptado para a verificação da existencia do header new_token.
- Para utilização nas rotas, registrar a middleware em `app/Http/Kernel.php` como `jwt` e registar a middleware na rota como `jwt:{guard}` (exemplo: `jwt:cliente`)
- No `config/auth.php`, registre a guard como `jwt`

##### Considerações
- Registrar sempre no .env as variaveis ``JWT_SECRET``, ``JWT_TTL``, ``JWT_REFRESH_TTL``, ``JWT_BLACKLIST_GRACE_PERIOD``
- Rodar o comando ``php artisan jwt:secret``

##### Exemplo de função na rota

    Route::group(['middleware' => 'jwt:admin'], function(){
        ...
    });

##### Exemplo em Kernel.php

    protected $routeMiddleware = [
        ...
        'jwt' => CheckToken::class
        ...
    ];

##### Exemplo de Guard

    'guards' => [
        ...
        'admin' => [
            'driver' => 'jwt',
            'provider' => 'admin'
        ],
        ...
    ],

##### Exemplo da middleware

    public function handle($request, \Closure $next, $guard = null)
    {
        $this->checkForToken($request); // Ver se tem o token

        if(!$guard){
            throw new \Exception('Guard inválido', 500);
        }

        Config::set('auth.defaults.guard', $guard);

        try {
            $user = $this->auth->parseToken()->getPayload()['sub'];
            if (!$this->checkModels($user->class, $guard) || !auth()->guard($guard)->onceUsingId($user->id)) { // Check user not found. Check token has expired.
                throw new UnauthorizedHttpException('jwt-auth', 'Usuario não encontrado'); //Se der problema com o token, virá um header chamado WWW-Authenticate com o valor de jwt-auth
            }
            return $next($request); // Se o usuario for autenticado e logado com token válido, continua request

        } catch (TokenExpiredException $t) { // Token expirado, usuario não logado
            $payload = $this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray(); // Pega os dados do token para autenticação

            $refreshed = JWTAuth::refresh(JWTAuth::getToken()); // Faz refresh do token
            $user = $payload['sub'];

            auth()->guard($guard)->onceUsingId($user->id); // Autentica pelo ID

            $response = $next($request); // Pega a request

            $response->header('new_token', $refreshed); // Adiciona o header com o novo token

            return $response; // Responde com o novo token no header

        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', 'Token Inválido', $e, Response::HTTP_UNAUTHORIZED); //Se der problema com o token, virá um header chamado WWW-Authenticate com o valor de jwt-auth
        }
    }

    public function getClassBybGuard($guard)
    {
        $provider = config('auth.guards.'.$guard.'.provider');
        $class = config('auth.providers.'.$provider.'.model');

        return $class;

    }

    public function checkModels($class, $guard)
    {
       $classAuth = $this->getClassBybGuard($guard);

       return $classAuth == $class;
    }

#### Routes

##### Objetivo

- Ter um arquivo de rota para cada um
- Gerenciar usuários logaveis no sistema
- Registrar os arquivos em ``app/Providers/RouteServiceProvider``

##### Diretório
- `routes`

##### Documentação
- Rotas: https://laravel.com/docs/5.7/routing

##### Exemplo de função na rota

    Route::post('/login', 'AdminController@login');
    Route::post('/solicitar-redefinir-senha', 'AdminController@solicitarRedefinirSenha');
    Route::post('/redefinir-senha', 'AdminController@redefinirSenha')->name('redefinir-senha');

    Route::group(['middleware' => 'jwt:admin'], function(){
        Route::get('/dashboard', 'DashboardController@dashboard');
        Route::get('/dashboard/oficinas/{cidade?}', 'DashboardController@getLatLongOficinasPelaCidade');

        Route::get('/me', 'AdminController@me');
        Route::get('/me/logout', 'AdminController@logout');
        Route::put('/me', 'AdminController@update');
        Route::delete('/me', 'AdminController@destroy');

        Route::get('configuracao', 'ConfiguracaoController@index');
        Route::put('configuracao', 'ConfiguracaoController@update');
    });

#### Exemplo de função para registrar na Service Provider

##### Considerações
- Sempre colocar a guard do jwt caso seja API
- Sempre colocar as coisas agrupadas usando `` Route::group();``

#### Generators

##### Objetivo

- Gerar Models
- Gerar Migrations

##### Documentação

- Pacote para Migration: https://github.com/Xethron/migrations-generator
- Pacote para Models: https://github.com/reliese/laravel

##### Considerações
- Sempre focar na modelagem do banco de dados
- Ler bem a documentação dos pacotes




