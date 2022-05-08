<?php
namespace EdcomsCMS\ContentBundle\Helpers;

/**
 * Description of Routing
 *
 * @author richard
 */
class RoutingHelper {
    /**
     * The regular expression used to compile and match URL's
     *
     * @type string
     */
    const ROUTE_COMPILE_REGEX = '`(\\\?(?:/|\.|))(?:\[([^:\]]*)(?::([^:\]]*))?\])(\?|)`';
    /**
     * The regular expression used to escape the non-named param section of a route URL
     *
     * @type string
     */
    const ROUTE_ESCAPE_REGEX = '`(?<=^|\])[^\]\[\?]+?(?=\[|$)`';

    /**
     * The types to detect in a defined match "block"
     *
     * Examples of these blocks are as follows:
     *
     * - integer:       '[i:id]'
     * - alphanumeric:  '[a:username]'
     * - hexadecimal:   '[h:color]'
     * - slug:          '[s:article]'
     *
     * @type array
     */
    protected $match_types = array(
        'i'  => '[0-9]++',
        'a'  => '[0-9A-Za-z]++',
        'h'  => '[0-9A-Fa-f]++',
        's'  => '[0-9A-Za-z-_]++',
        '*'  => '.+?',
        '**' => '.++',
        ''   => '[^/]+?'
    );
    
    protected $routes = [];
    /**
     *
     * @var Output 
     */
    protected $output;
    public function __construct($appDir)
    {
        $this->routes = Yaml::parse(file_get_contents($appDir.'/routes.yml'));
        
        $route = Common::Get('REQUEST_URI', INPUT_SERVER);
        $method = Common::Get('REQUEST_METHOD', INPUT_SERVER);
        $this->TriggerRoute($route, $method);
        
    }
    /**
     * 
     * @return Output
     */
    public function GetOutput()
    {
        return $this->output;
    }
    /**
     * Run the action associated to the route
     * @param string $route
     */
    private function TriggerRoute($route='/', $method='GET')
    {
        $skip_num = 0;
        $methods_matched = [];
        $req_method = $method;
        $uri = $route;
        $params = [];
        $apc = function_exists('apc_fetch');
        foreach ($this->routes as $name=>$route) {
            // Are we skipping any matches?
            if ($skip_num > 0) {
                $skip_num--;
                continue;
            }
            // Grab the properties of the route handler
            $method = $route['type'];
            $path = $route['route'];
            $count_match = (isset($route['count_match'])) ? $route['count_match'] : 0;
            // Keep track of whether this specific request method was matched
            $method_match = null;
            // Was a method specified? If so, check it against the current request method
            if (is_array($method)) {
                foreach ($method as $test) {
                    if (strcasecmp($req_method, $test) === 0) {
                        $method_match = true;
                    } elseif (strcasecmp($req_method, 'HEAD') === 0
                          && (strcasecmp($test, 'HEAD') === 0 || strcasecmp($test, 'GET') === 0)) {
                        // Test for HEAD request (like GET)
                        $method_match = true;
                    }
                }
                if (null === $method_match) {
                    $method_match = false;
                }
            } elseif (null !== $method && strcasecmp($req_method, $method) !== 0) {
                $method_match = false;
                // Test for HEAD request (like GET)
                if (strcasecmp($req_method, 'HEAD') === 0
                    && (strcasecmp($method, 'HEAD') === 0 || strcasecmp($method, 'GET') === 0 )) {
                    $method_match = true;
                }
            } elseif (null !== $method && strcasecmp($req_method, $method) === 0) {
                $method_match = true;
            }
            // If the method was matched or if it wasn't even passed (in the route callback)
            $possible_match = (null === $method_match) || $method_match;
            // ! is used to negate a match
            if (isset($path[0]) && $path[0] === '!') {
                $negate = true;
                $i = 1;
            } else {
                $negate = false;
                $i = 0;
            }

            if (!$possible_match) {
                // Don't bother counting this as a method match if the route isn't supposed to match anyway
                if ($count_match) {
                    // Keep track of possibly matched methods
                    $methods_matched = array_merge($methods_matched, (array) $method);
                    $methods_matched = array_filter($methods_matched);
                    $methods_matched = array_unique($methods_matched);
                }
                continue;
            }
            $expression = null;
            $regex = false;
            $j = 0;
            $n = isset($path[$i]) ? $path[$i] : null;
            // Find the longest non-regex substring and match it against the URI
            while (true) {
                if (!isset($path[$i])) {
                    break;
                } elseif (false === $regex) {
                    $c = $n;
                    $regex = $c === '[' || $c === '(' || $c === '.';
                    if (false === $regex && false !== isset($path[$i+1])) {
                        $n = $path[$i + 1];
                        $regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
                    }
                    if (false === $regex && $c !== '/' && (!isset($uri[$j]) || $c !== $uri[$j])) {
                        continue 2;
                    }
                    $j++;
                }
                $expression .= $path[$i++];
            }
            // Check if there's a cached regex string
            if (false !== $apc) {
                $regex = apc_fetch("route:$expression");
                if (false === $regex) {
                    $regex = $this->CompileRoute($expression);
                    apc_store("route:$expression", $regex);
                }
            } else {
                $regex = $this->CompileRoute($expression);
            }
            $match = preg_match($regex, $uri, $params);
            if (isset($match) && $match ^ $negate) {
                if (!empty($params)) {
                    /**
                     * URL Decode the params according to RFC 3986
                     * @link http://www.faqs.org/rfcs/rfc3986
                     *
                     * Decode here AFTER matching as per @chriso's suggestion
                     * @link https://github.com/chriso/klein.php/issues/117#issuecomment-21093915
                     */
                    $params = array_map('rawurldecode', $params);
                }
                $this->Dispatch($route, array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY));
            }
        }
    }
    private function CompileRoute($route)
    {
        // First escape all of the non-named param (non [block]s) for regex-chars
        $route = preg_replace_callback(
            static::ROUTE_ESCAPE_REGEX,
            function ($match) {
                return preg_quote($match[0]);
            },
            $route
        );
        // Get a local reference of the match types to pass into our closure
        $match_types = $this->match_types;
        // Now let's actually compile the path
        $route = preg_replace_callback(
            static::ROUTE_COMPILE_REGEX,
            function ($match) use ($match_types) {
                list(, $pre, $type, $param, $optional) = $match;
                if (isset($match_types[$type])) {
                    $type = $match_types[$type];
                }
                // Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                         . ($pre !== '' ? $pre : null)
                         . '('
                         . ($param !== '' ? "?P<$param>" : null)
                         . $type
                         . '))'
                         . ($optional !== '' ? '?' : null);
                return $pattern;
            },
            $route
        );
        $regex = "`^$route$`";
        // Check if our regular expression is valid
        Common::ValidateRegularExpression($regex);
        return $regex;
    }
    private function Dispatch($route, $params)
    {
        if (class_exists($route['class'])) {
            $Trigger = new $route['class']();
            $Method = $route['method'];
            $this->TriggerMethod($Trigger, $Method, $params);
        }
    }
    private function TriggerMethod(Base $Trigger, $Method, $params)
    {
        if (method_exists($Trigger, $Method)) {
            $params = $this->PrepareParams($params);
            $this->SetOutput(call_user_func_array(array(&$Trigger, $Method), $params));
        }
    }
    /**
     * 
     * @param \Terminal\Controller\Output $Output
     */
    private function SetOutput(Output $Output)
    {
        $this->output = $Output;
    }
    private function PrepareParams($params)
    {
        // this method returns an array of common parameters plus any URL field properties \\
        return array_merge([], $params);
    }
}
