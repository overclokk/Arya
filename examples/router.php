<?php

use Arya\Application,
    Arya\Request,
    Arya\Response;

require __DIR__ . '/../autoload.php';

function helloFunction(Request $request) {
    return '<html><body><h1>Hello, World.</h1></body></html>';
}

$lambda = function(Request $request) {
    return '<html><body><h1>Hello from $lambda!</h1></body></html>';
};

$vars = function(Request $request) {
    $vars = $request->getAllVars();
    return sprintf("<html><body><pre>%s</pre></body></html>", print_r($vars, TRUE));
};

class MyClass {
    function staticMethod(Request $request) {
        $msg = 'Hello from MyClass::staticMethod()';
        $body = sprintf("<html><body><h1>%s</h1</body></html>", $msg);
        return new Response($body, $status = 200);
    }
}

class CtorDependencies {
    private $request;
    function __construct(Request $request) {
        $this->request = $request;
    }
    function myInstanceMethod() {
        $headline = "Hello from CtorDependencies::someMethod()";
        $subheading = "Composed Request Vars";

        return sprintf(
            "<html><body><h1>%s</h1><hr/><h4>%s</h4><pre>%s</pre></body></html>",
            $headline,
            $subheading,
            print_r($this->request->getAllVars(), TRUE)
        );
    }
}

function complexResponse(Request $request) {
    $response = (new Response)
        ->setHeader('X-My-Header', 'some-value')
        ->addHeader('X-My-Header', 'HTTP header fields can have multiple values!')
        ->setAllHeaders(array(
            'Header1' => 'header 1 value',
            'Header2' => 'header 2 value',
        ))
        ->setStatus(200)
        ->setReason('OK')
        ->setBody('<html><body><h1>Complex Response</h1></body></html>')
    ;

    return $response;
}

function argFunction(Request $request, $arg1) {
    $routeArgs = print_r($request['ROUTE_ARGS'], TRUE);
    $funcArg = print_r($arg1, TRUE);
    $body = sprintf('$request[\'ROUTE_ARGS\']: %s | $funcArgs: %s', $routeArgs, $funcArg);

    return (new Response)
        ->setHeader('Content-Type', 'text/plain;charset=utf-8')
        ->setBody($body)
    ;
}

function numericArgsFunction(Request $request) {
    $body = "<html><body><h1>numericArgsFunction</h1><pre>%s</pre></body></html>";
    $routeArgs = print_r($request['ROUTE_ARGS'], TRUE);

    return sprintf($body, $routeArgs);
}

function output() {
    echo "Output generated by your application generates an error";

    return "You won't see this because the output will generate a 500 error";
}

$app = (new Application)
    ->route('GET', '/', 'helloFunction')
    ->route('GET', '/lambda', $lambda)
    ->route('GET', '/static-method', 'MyClass::staticMethod')
    ->route('GET', '/ctor-deps', 'CtorDependencies::myInstanceMethod')
    ->route('GET', '/complex-response', 'complexResponse')
    ->route('GET', '/$arg1', 'argFunction')
    ->route('GET', '/$#arg1/$#arg2', 'numericArgsFunction')
    ->route('GET', '/output', 'output')
    ->run()
;