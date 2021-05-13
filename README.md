## Библиотека-обертка для создания API-модулей, основанная на Guzzle

### Пример использования

Response:

```php
namespace SomeModules\Client;

use Adapterap\GuzzleClient\GuzzleClientResponse;

class SomeModuleClientResponse extends GuzzleClientResponse {
    // Как правило, в этом классе нечего менять
}
```

Request:

```php
namespace SomeModule\Client;

use Adapterap\GuzzleClient\Exceptions\GuzzleClientException;
use Adapterap\GuzzleClient\GuzzleClientRequest;
use GuzzleHttp\RequestOptions;

class SomeModuleClientRequest extends GuzzleClientRequest {
    /**
     * Имя класса, который будет обрабатывать ответ от сервера.
     *
     * @var string
     */
    protected string $responseClassName = SomeModuleClientResponse::class;
    
    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string $method HTTP method.
     * @param string $url
     * @param array $options Request options to apply. See \GuzzleHttp\RequestOptions.
     *
     * @return SomeModuleClientResponse
     * @throws GuzzleClientException
     */
    public function request(string $method, string $url, array $options = []) : SomeModuleClientResponse {
        // Добавляем необходимые заголовки
        $options[RequestOptions::HEADERS] ??= [];
        $options[RequestOptions::HEADERS]['X-TOKEN'] = '...';
    
        return parent::request($method,$url,$options);
    }
}
```

Some Action:

```php
namespace SomeModule\Client\Request;

class Users {
    /**
     * Объект-запрос для работы с HTTP.
     * 
     * @var SomeModuleClientRequest 
     */
    protected SomeModuleClientRequest $request;
    
    /**
     * Users constructor.
     * @param SomeModuleClientRequest $request
     */
    public function __construct(SomeModuleClientRequest $request) {
        $this->request = $request;
    }
    
    /**
     * Аутентификация.
     *
     * @param string $email
     * @param string $password 
     * 
     * @return array
     */
    public function login(string $email, string $password): array {
        $response = $this->request->post('/login', [/* ... */]);
        
        return $response->toArray();
    }
}
```

Client:
```php
namespace SomeModule;

use Adapterap\GuzzleClient\GuzzleClient;

class SomeModuleClient extends GuzzleClient {
    /**
     * Класс, для отправки HTTP запросов.
     *  
     * @var SomeModuleClientRequest|null 
     */
    protected ?SomeModuleClientRequest $request = null;
    
    /**
     * Action для работы с пользователями.
     * 
     * @return Users
     */
    public function users(): Users {
        return new Users($this->getRequest());
    }

    /**
     * Формирует и возвращает объект для отправки запросов.
     *
     * @return SomeModuleClientRequest
     */
    protected function getRequest() : SomeModuleClientRequest{
        if ($this->request === null) {
            $this->request = new SomeModuleClientRequest();
        }
        
        return $this->request();
    }
}
```

Использование:
```php
$email = 'user@example.com';
$password = 'secret';

$client = app(SomeModuleClient::class);
$response = $client->users()->login($email, $password);
```
