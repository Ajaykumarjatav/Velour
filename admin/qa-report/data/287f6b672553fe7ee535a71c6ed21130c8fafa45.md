# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: admin-pages.spec.ts >> Super Admin pages (plan SA-*) >> SA-18b GET /admin/audit/stats
- Location: tests\playwright\admin-pages.spec.ts:44:5

# Error details

```
Error: SA-18b admin/audit/stats status

expect(received).toBeLessThan(expected)

Expected: < 500
Received:   500
```

# Page snapshot

```yaml
- generic [ref=e2]:
  - banner [ref=e3]:
    - generic [ref=e5]:
      - generic [ref=e6]: Internal Server Error
      - button [ref=e13] [cursor=pointer]
  - main [ref=e16]:
    - generic [ref=e17]:
      - generic [ref=e19]:
        - generic [ref=e20]:
          - generic [ref=e21]: InvalidArgumentException
          - generic [ref=e22]: View [admin.audit.stats] not found.
        - generic [ref=e23]:
          - generic [ref=e24]: GET localhost
          - generic [ref=e26]: PHP 8.2.12 — Laravel 11.51.0
      - generic [ref=e29]:
        - generic [ref=e31]:
          - button "Expand vendor frames" [ref=e33] [cursor=pointer]:
            - generic [ref=e34]: Expand
            - generic [ref=e35]: vendor frames
          - generic [ref=e41]:
            - button "Illuminate\\View\\FileViewFinder :139 findInPaths" [ref=e42] [cursor=pointer]:
              - generic [ref=e44]:
                - generic [ref=e46]:
                  - generic [ref=e47]: Illuminate\View\FileViewFinder
                  - generic [ref=e48]: :139
                - generic [ref=e49]: findInPaths
            - button "Illuminate\\View\\FileViewFinder :79 find" [ref=e50] [cursor=pointer]:
              - generic [ref=e52]:
                - generic [ref=e54]:
                  - generic [ref=e55]: Illuminate\View\FileViewFinder
                  - generic [ref=e56]: :79
                - generic [ref=e57]: find
            - button "Illuminate\\View\\Factory :151 make" [ref=e58] [cursor=pointer]:
              - generic [ref=e60]:
                - generic [ref=e62]:
                  - generic [ref=e63]: Illuminate\View\Factory
                  - generic [ref=e64]: :151
                - generic [ref=e65]: make
            - button "C:\\xampp\\htdocs\\vellor\\admin\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\helpers.php :1062 view" [ref=e66] [cursor=pointer]:
              - generic [ref=e68]:
                - generic [ref=e70]:
                  - generic [ref=e71]: C:\xampp\htdocs\vellor\admin\vendor\laravel\framework\src\Illuminate\Foundation\helpers.php
                  - generic [ref=e72]: :1062
                - generic [ref=e73]: view
            - button "App\\Http\\Controllers\\Admin\\AuditLogController :114 stats" [ref=e74] [cursor=pointer]:
              - generic [ref=e76]:
                - generic [ref=e78]:
                  - generic [ref=e79]: App\Http\Controllers\Admin\AuditLogController
                  - generic [ref=e80]: :114
                - generic [ref=e81]: stats
            - button "Illuminate\\Routing\\Controller :54 callAction" [ref=e82] [cursor=pointer]:
              - generic [ref=e84]:
                - generic [ref=e86]:
                  - generic [ref=e87]: Illuminate\Routing\Controller
                  - generic [ref=e88]: :54
                - generic [ref=e89]: callAction
            - button "Illuminate\\Routing\\ControllerDispatcher :44 dispatch" [ref=e90] [cursor=pointer]:
              - generic [ref=e92]:
                - generic [ref=e94]:
                  - generic [ref=e95]: Illuminate\Routing\ControllerDispatcher
                  - generic [ref=e96]: :44
                - generic [ref=e97]: dispatch
            - button "Illuminate\\Routing\\Route :266 runController" [ref=e98] [cursor=pointer]:
              - generic [ref=e100]:
                - generic [ref=e102]:
                  - generic [ref=e103]: Illuminate\Routing\Route
                  - generic [ref=e104]: :266
                - generic [ref=e105]: runController
            - button "Illuminate\\Routing\\Route :212 run" [ref=e106] [cursor=pointer]:
              - generic [ref=e108]:
                - generic [ref=e110]:
                  - generic [ref=e111]: Illuminate\Routing\Route
                  - generic [ref=e112]: :212
                - generic [ref=e113]: run
            - 'button "Illuminate\\Routing\\Router :808 Illuminate\\Routing\\{closure}" [ref=e114] [cursor=pointer]':
              - generic [ref=e116]:
                - generic [ref=e118]:
                  - generic [ref=e119]: Illuminate\Routing\Router
                  - generic [ref=e120]: :808
                - generic [ref=e121]: "Illuminate\\Routing\\{closure}"
            - 'button "Illuminate\\Pipeline\\Pipeline :170 Illuminate\\Pipeline\\{closure}" [ref=e122] [cursor=pointer]':
              - generic [ref=e124]:
                - generic [ref=e126]:
                  - generic [ref=e127]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e128]: :170
                - generic [ref=e129]: "Illuminate\\Pipeline\\{closure}"
            - button "C:\\xampp\\htdocs\\vellor\\admin\\app\\Http\\Middleware\\LogUserActivity.php :16 handle" [ref=e130] [cursor=pointer]:
              - generic [ref=e132]:
                - generic [ref=e134]:
                  - generic [ref=e135]: C:\xampp\htdocs\vellor\admin\app\Http\Middleware\LogUserActivity.php
                  - generic [ref=e136]: :16
                - generic [ref=e137]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e138] [cursor=pointer]':
              - generic [ref=e140]:
                - generic [ref=e142]:
                  - generic [ref=e143]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e144]: :209
                - generic [ref=e145]: "Illuminate\\Pipeline\\{closure}"
            - button "App\\Http\\Middleware\\SuperAdminMiddleware :41 handle" [ref=e146] [cursor=pointer]:
              - generic [ref=e148]:
                - generic [ref=e150]:
                  - generic [ref=e151]: App\Http\Middleware\SuperAdminMiddleware
                  - generic [ref=e152]: :41
                - generic [ref=e153]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e154] [cursor=pointer]':
              - generic [ref=e156]:
                - generic [ref=e158]:
                  - generic [ref=e159]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e160]: :209
                - generic [ref=e161]: "Illuminate\\Pipeline\\{closure}"
            - button "C:\\xampp\\htdocs\\vellor\\admin\\app\\Http\\Middleware\\EnsurePasswordChange.php :15 handle" [ref=e162] [cursor=pointer]:
              - generic [ref=e164]:
                - generic [ref=e166]:
                  - generic [ref=e167]: C:\xampp\htdocs\vellor\admin\app\Http\Middleware\EnsurePasswordChange.php
                  - generic [ref=e168]: :15
                - generic [ref=e169]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e170] [cursor=pointer]':
              - generic [ref=e172]:
                - generic [ref=e174]:
                  - generic [ref=e175]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e176]: :209
                - generic [ref=e177]: "Illuminate\\Pipeline\\{closure}"
            - button "App\\Http\\Middleware\\RequireTwoFactor :36 handle" [ref=e178] [cursor=pointer]:
              - generic [ref=e180]:
                - generic [ref=e182]:
                  - generic [ref=e183]: App\Http\Middleware\RequireTwoFactor
                  - generic [ref=e184]: :36
                - generic [ref=e185]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e186] [cursor=pointer]':
              - generic [ref=e188]:
                - generic [ref=e190]:
                  - generic [ref=e191]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e192]: :209
                - generic [ref=e193]: "Illuminate\\Pipeline\\{closure}"
            - button "App\\Http\\Middleware\\EnsureEmailIsVerified :27 handle" [ref=e194] [cursor=pointer]:
              - generic [ref=e196]:
                - generic [ref=e198]:
                  - generic [ref=e199]: App\Http\Middleware\EnsureEmailIsVerified
                  - generic [ref=e200]: :27
                - generic [ref=e201]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e202] [cursor=pointer]':
              - generic [ref=e204]:
                - generic [ref=e206]:
                  - generic [ref=e207]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e208]: :209
                - generic [ref=e209]: "Illuminate\\Pipeline\\{closure}"
            - button "C:\\xampp\\htdocs\\vellor\\admin\\app\\Http\\Middleware\\ForgetLegacySessionCookies.php :31 handle" [ref=e210] [cursor=pointer]:
              - generic [ref=e212]:
                - generic [ref=e214]:
                  - generic [ref=e215]: C:\xampp\htdocs\vellor\admin\app\Http\Middleware\ForgetLegacySessionCookies.php
                  - generic [ref=e216]: :31
                - generic [ref=e217]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e218] [cursor=pointer]':
              - generic [ref=e220]:
                - generic [ref=e222]:
                  - generic [ref=e223]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e224]: :209
                - generic [ref=e225]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Routing\\Middleware\\SubstituteBindings :51 handle" [ref=e226] [cursor=pointer]:
              - generic [ref=e228]:
                - generic [ref=e230]:
                  - generic [ref=e231]: Illuminate\Routing\Middleware\SubstituteBindings
                  - generic [ref=e232]: :51
                - generic [ref=e233]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e234] [cursor=pointer]':
              - generic [ref=e236]:
                - generic [ref=e238]:
                  - generic [ref=e239]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e240]: :209
                - generic [ref=e241]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Auth\\Middleware\\Authenticate :64 handle" [ref=e242] [cursor=pointer]:
              - generic [ref=e244]:
                - generic [ref=e246]:
                  - generic [ref=e247]: Illuminate\Auth\Middleware\Authenticate
                  - generic [ref=e248]: :64
                - generic [ref=e249]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e250] [cursor=pointer]':
              - generic [ref=e252]:
                - generic [ref=e254]:
                  - generic [ref=e255]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e256]: :209
                - generic [ref=e257]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken :88 handle" [ref=e258] [cursor=pointer]:
              - generic [ref=e260]:
                - generic [ref=e262]:
                  - generic [ref=e263]: Illuminate\Foundation\Http\Middleware\VerifyCsrfToken
                  - generic [ref=e264]: :88
                - generic [ref=e265]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e266] [cursor=pointer]':
              - generic [ref=e268]:
                - generic [ref=e270]:
                  - generic [ref=e271]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e272]: :209
                - generic [ref=e273]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\View\\Middleware\\ShareErrorsFromSession :49 handle" [ref=e274] [cursor=pointer]:
              - generic [ref=e276]:
                - generic [ref=e278]:
                  - generic [ref=e279]: Illuminate\View\Middleware\ShareErrorsFromSession
                  - generic [ref=e280]: :49
                - generic [ref=e281]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e282] [cursor=pointer]':
              - generic [ref=e284]:
                - generic [ref=e286]:
                  - generic [ref=e287]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e288]: :209
                - generic [ref=e289]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Session\\Middleware\\StartSession :121 handleStatefulRequest" [ref=e290] [cursor=pointer]:
              - generic [ref=e292]:
                - generic [ref=e294]:
                  - generic [ref=e295]: Illuminate\Session\Middleware\StartSession
                  - generic [ref=e296]: :121
                - generic [ref=e297]: handleStatefulRequest
            - button "Illuminate\\Session\\Middleware\\StartSession :64 handle" [ref=e298] [cursor=pointer]:
              - generic [ref=e300]:
                - generic [ref=e302]:
                  - generic [ref=e303]: Illuminate\Session\Middleware\StartSession
                  - generic [ref=e304]: :64
                - generic [ref=e305]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e306] [cursor=pointer]':
              - generic [ref=e308]:
                - generic [ref=e310]:
                  - generic [ref=e311]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e312]: :209
                - generic [ref=e313]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse :37 handle" [ref=e314] [cursor=pointer]:
              - generic [ref=e316]:
                - generic [ref=e318]:
                  - generic [ref=e319]: Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse
                  - generic [ref=e320]: :37
                - generic [ref=e321]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e322] [cursor=pointer]':
              - generic [ref=e324]:
                - generic [ref=e326]:
                  - generic [ref=e327]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e328]: :209
                - generic [ref=e329]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Cookie\\Middleware\\EncryptCookies :75 handle" [ref=e330] [cursor=pointer]:
              - generic [ref=e332]:
                - generic [ref=e334]:
                  - generic [ref=e335]: Illuminate\Cookie\Middleware\EncryptCookies
                  - generic [ref=e336]: :75
                - generic [ref=e337]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e338] [cursor=pointer]':
              - generic [ref=e340]:
                - generic [ref=e342]:
                  - generic [ref=e343]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e344]: :209
                - generic [ref=e345]: "Illuminate\\Pipeline\\{closure}"
            - button "C:\\xampp\\htdocs\\vellor\\admin\\app\\Http\\Middleware\\PreventPublicSessionClobber.php :30 handle" [ref=e346] [cursor=pointer]:
              - generic [ref=e348]:
                - generic [ref=e350]:
                  - generic [ref=e351]: C:\xampp\htdocs\vellor\admin\app\Http\Middleware\PreventPublicSessionClobber.php
                  - generic [ref=e352]: :30
                - generic [ref=e353]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e354] [cursor=pointer]':
              - generic [ref=e356]:
                - generic [ref=e358]:
                  - generic [ref=e359]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e360]: :209
                - generic [ref=e361]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Pipeline\\Pipeline :127 then" [ref=e362] [cursor=pointer]:
              - generic [ref=e364]:
                - generic [ref=e366]:
                  - generic [ref=e367]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e368]: :127
                - generic [ref=e369]: then
            - button "Illuminate\\Routing\\Router :807 runRouteWithinStack" [ref=e370] [cursor=pointer]:
              - generic [ref=e372]:
                - generic [ref=e374]:
                  - generic [ref=e375]: Illuminate\Routing\Router
                  - generic [ref=e376]: :807
                - generic [ref=e377]: runRouteWithinStack
            - button "Illuminate\\Routing\\Router :786 runRoute" [ref=e378] [cursor=pointer]:
              - generic [ref=e380]:
                - generic [ref=e382]:
                  - generic [ref=e383]: Illuminate\Routing\Router
                  - generic [ref=e384]: :786
                - generic [ref=e385]: runRoute
            - button "Illuminate\\Routing\\Router :750 dispatchToRoute" [ref=e386] [cursor=pointer]:
              - generic [ref=e388]:
                - generic [ref=e390]:
                  - generic [ref=e391]: Illuminate\Routing\Router
                  - generic [ref=e392]: :750
                - generic [ref=e393]: dispatchToRoute
            - button "Illuminate\\Routing\\Router :739 dispatch" [ref=e394] [cursor=pointer]:
              - generic [ref=e396]:
                - generic [ref=e398]:
                  - generic [ref=e399]: Illuminate\Routing\Router
                  - generic [ref=e400]: :739
                - generic [ref=e401]: dispatch
            - 'button "Illuminate\\Foundation\\Http\\Kernel :201 Illuminate\\Foundation\\Http\\{closure}" [ref=e402] [cursor=pointer]':
              - generic [ref=e404]:
                - generic [ref=e406]:
                  - generic [ref=e407]: Illuminate\Foundation\Http\Kernel
                  - generic [ref=e408]: :201
                - generic [ref=e409]: "Illuminate\\Foundation\\Http\\{closure}"
            - 'button "Illuminate\\Pipeline\\Pipeline :170 Illuminate\\Pipeline\\{closure}" [ref=e410] [cursor=pointer]':
              - generic [ref=e412]:
                - generic [ref=e414]:
                  - generic [ref=e415]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e416]: :170
                - generic [ref=e417]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest :21 handle" [ref=e418] [cursor=pointer]:
              - generic [ref=e420]:
                - generic [ref=e422]:
                  - generic [ref=e423]: Illuminate\Foundation\Http\Middleware\TransformsRequest
                  - generic [ref=e424]: :21
                - generic [ref=e425]: handle
            - button "Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull :31 handle" [ref=e426] [cursor=pointer]:
              - generic [ref=e428]:
                - generic [ref=e430]:
                  - generic [ref=e431]: Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull
                  - generic [ref=e432]: :31
                - generic [ref=e433]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e434] [cursor=pointer]':
              - generic [ref=e436]:
                - generic [ref=e438]:
                  - generic [ref=e439]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e440]: :209
                - generic [ref=e441]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest :21 handle" [ref=e442] [cursor=pointer]:
              - generic [ref=e444]:
                - generic [ref=e446]:
                  - generic [ref=e447]: Illuminate\Foundation\Http\Middleware\TransformsRequest
                  - generic [ref=e448]: :21
                - generic [ref=e449]: handle
            - button "Illuminate\\Foundation\\Http\\Middleware\\TrimStrings :51 handle" [ref=e450] [cursor=pointer]:
              - generic [ref=e452]:
                - generic [ref=e454]:
                  - generic [ref=e455]: Illuminate\Foundation\Http\Middleware\TrimStrings
                  - generic [ref=e456]: :51
                - generic [ref=e457]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e458] [cursor=pointer]':
              - generic [ref=e460]:
                - generic [ref=e462]:
                  - generic [ref=e463]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e464]: :209
                - generic [ref=e465]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Http\\Middleware\\ValidatePostSize :27 handle" [ref=e466] [cursor=pointer]:
              - generic [ref=e468]:
                - generic [ref=e470]:
                  - generic [ref=e471]: Illuminate\Http\Middleware\ValidatePostSize
                  - generic [ref=e472]: :27
                - generic [ref=e473]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e474] [cursor=pointer]':
              - generic [ref=e476]:
                - generic [ref=e478]:
                  - generic [ref=e479]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e480]: :209
                - generic [ref=e481]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance :110 handle" [ref=e482] [cursor=pointer]:
              - generic [ref=e484]:
                - generic [ref=e486]:
                  - generic [ref=e487]: Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance
                  - generic [ref=e488]: :110
                - generic [ref=e489]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e490] [cursor=pointer]':
              - generic [ref=e492]:
                - generic [ref=e494]:
                  - generic [ref=e495]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e496]: :209
                - generic [ref=e497]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Http\\Middleware\\HandleCors :49 handle" [ref=e498] [cursor=pointer]:
              - generic [ref=e500]:
                - generic [ref=e502]:
                  - generic [ref=e503]: Illuminate\Http\Middleware\HandleCors
                  - generic [ref=e504]: :49
                - generic [ref=e505]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e506] [cursor=pointer]':
              - generic [ref=e508]:
                - generic [ref=e510]:
                  - generic [ref=e511]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e512]: :209
                - generic [ref=e513]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Http\\Middleware\\TrustProxies :58 handle" [ref=e514] [cursor=pointer]:
              - generic [ref=e516]:
                - generic [ref=e518]:
                  - generic [ref=e519]: Illuminate\Http\Middleware\TrustProxies
                  - generic [ref=e520]: :58
                - generic [ref=e521]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e522] [cursor=pointer]':
              - generic [ref=e524]:
                - generic [ref=e526]:
                  - generic [ref=e527]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e528]: :209
                - generic [ref=e529]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks :22 handle" [ref=e530] [cursor=pointer]:
              - generic [ref=e532]:
                - generic [ref=e534]:
                  - generic [ref=e535]: Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks
                  - generic [ref=e536]: :22
                - generic [ref=e537]: handle
            - 'button "Illuminate\\Pipeline\\Pipeline :209 Illuminate\\Pipeline\\{closure}" [ref=e538] [cursor=pointer]':
              - generic [ref=e540]:
                - generic [ref=e542]:
                  - generic [ref=e543]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e544]: :209
                - generic [ref=e545]: "Illuminate\\Pipeline\\{closure}"
            - button "Illuminate\\Pipeline\\Pipeline :127 then" [ref=e546] [cursor=pointer]:
              - generic [ref=e548]:
                - generic [ref=e550]:
                  - generic [ref=e551]: Illuminate\Pipeline\Pipeline
                  - generic [ref=e552]: :127
                - generic [ref=e553]: then
            - button "Illuminate\\Foundation\\Http\\Kernel :176 sendRequestThroughRouter" [ref=e554] [cursor=pointer]:
              - generic [ref=e556]:
                - generic [ref=e558]:
                  - generic [ref=e559]: Illuminate\Foundation\Http\Kernel
                  - generic [ref=e560]: :176
                - generic [ref=e561]: sendRequestThroughRouter
            - button "Illuminate\\Foundation\\Http\\Kernel :145 handle" [ref=e562] [cursor=pointer]:
              - generic [ref=e564]:
                - generic [ref=e566]:
                  - generic [ref=e567]: Illuminate\Foundation\Http\Kernel
                  - generic [ref=e568]: :145
                - generic [ref=e569]: handle
            - button "C:\\xampp\\htdocs\\vellor\\admin\\public\\index.php :11" [ref=e570] [cursor=pointer]:
              - generic [ref=e574]:
                - generic [ref=e575]: C:\xampp\htdocs\vellor\admin\public\index.php
                - generic [ref=e576]: :11
        - generic [ref=e577]:
          - generic [ref=e578]: C:\xampp\htdocs\vellor\admin\vendor\laravel\framework\src\Illuminate\View\FileViewFinder.php :139
          - code [ref=e583]:
            - table [ref=e584]:
              - rowgroup [ref=e585]:
                - row [ref=e586]:
                  - cell "134" [ref=e587]
                  - cell "return $viewPath;" [ref=e589]
                - row [ref=e590]:
                  - cell "135" [ref=e591]
                  - 'cell "}" [ref=e593]'
                - row [ref=e594]:
                  - cell "136" [ref=e595]
                  - 'cell "}" [ref=e597]'
                - row [ref=e598]:
                  - cell "137" [ref=e599]
                  - 'cell "}" [ref=e601]'
                - row [ref=e602]:
                  - cell "138" [ref=e603]
                  - cell [ref=e605]
                - row [ref=e606]:
                  - cell "139" [ref=e607]
                  - 'cell "throw new InvalidArgumentException(\"View [{$name}] not found.\");" [ref=e609]':
                    - text: throw new InvalidArgumentException(
                    - generic [ref=e610]: "\"View [{$name}] not found.\""
                    - text: );
                - row [ref=e611]:
                  - cell "140" [ref=e612]
                  - 'cell "}" [ref=e614]'
                - row [ref=e615]:
                  - cell "141" [ref=e616]
                  - cell [ref=e618]
                - row [ref=e619]:
                  - cell "142" [ref=e620]
                  - cell "/**" [ref=e622]
                - row [ref=e624]:
                  - cell "143" [ref=e625]
                  - cell "* Get an array of possible view files." [ref=e627]
                - row [ref=e628]:
                  - cell "144" [ref=e629]
                  - cell "*" [ref=e631]
                - row [ref=e632]:
                  - cell "145" [ref=e633]
                  - cell "* @param string $name" [ref=e635]
                - row [ref=e637]:
                  - cell "146" [ref=e638]
                  - cell "* @return array" [ref=e640]
                - row [ref=e642]:
                  - cell "147" [ref=e643]
                  - cell "*/" [ref=e645]
                - row [ref=e646]:
                  - cell "148" [ref=e647]
                  - cell "protected function getPossibleViewFiles($name)" [ref=e649]:
                    - text: protected
                    - generic [ref=e650]:
                      - text: function getPossibleViewFiles(
                      - generic [ref=e651]: $name
                      - text: )
                - row [ref=e652]:
                  - cell "149" [ref=e653]
                  - 'cell "{" [ref=e655]'
                - row [ref=e656]:
                  - cell "150" [ref=e657]
                  - cell "return array_map(fn ($extension) => str_replace('.', '/', $name).'.'.$extension, $this->extensions);" [ref=e659]
        - text: 74 75 76 77 78 79 80 81 82 83 84 85 86 87 88 89 90 146 147 148 149 150 151 152 153 154 155 156 157 158 159 160 161 162 1057 1058 1059 1060 1061 1062 1063 1064 109 110 111 112 113 114 115 116 117 118 119 120 121 122 123 124 125 49 50 51 52 53 54 55 56 57 58 59 60 61 62 63 64 65 39 40 41 42 43 44 45 46 47 48 49 50 51 52 53 54 55 56 261 262 263 264 265 266 267 268 269 270 271 272 273 274 275 276 277 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 222 223 224 803 804 805 806 807 808 809 810 811 812 813 814 815 816 817 818 819 165 166 167 168 169 170 171 172 173 174 175 176 177 178 179 180 181 11 12 13 14 15 16 17 18 19 20 21 22 23 24 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 36 37 38 39 40 41 42 43 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 31 32 33 34 35 36 37 38 39 40 41 42 43 44 45 46 47 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36 37 38 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 26 27 28 29 30 31 32 33 34 35 36 37 38 39 40 41 42 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 46 47 48 49 50 51 52 53 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 59 60 61 62 63 64 65 66 67 68 69 70 71 72 73 74 75 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 83 84 85 86 87 88 89 90 91 92 93 94 95 96 97 98 99 100 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 44 45 46 47 48 49 50 51 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 116 117 118 119 120 121 122 123 124 125 126 127 128 129 130 131 132 59 60 61 62 63 64 65 66 67 68 69 70 71 72 73 74 75 76 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 32 33 34 35 36 37 38 39 40 41 42 43 44 45 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 70 71 72 73 74 75 76 77 78 79 80 81 82 83 84 85 86 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 25 26 27 28 29 30 31 32 33 34 35 36 37 38 39 40 41 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 122 123 124 125 126 127 128 129 130 131 132 133 134 135 136 137 138 139 802 803 804 805 806 807 808 809 810 811 812 813 814 815 816 817 818 819 781 782 783 784 785 786 787 788 789 790 791 792 793 794 795 796 797 798 745 746 747 748 749 750 751 752 753 754 755 756 757 758 759 760 761 734 735 736 737 738 739 740 741 742 743 744 745 746 747 748 749 750 196 197 198 199 200 201 202 203 204 205 206 207 208 209 210 211 212 213 165 166 167 168 169 170 171 172 173 174 175 176 177 178 179 180 181 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 26 27 28 29 30 31 32 33 34 35 36 37 38 39 40 41 42 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 46 47 48 49 50 51 52 53 54 55 56 57 58 59 60 61 62 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36 37 38 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 105 106 107 108 109 110 111 112 113 114 115 116 117 118 119 120 121 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 44 45 46 47 48 49 50 51 52 53 54 55 56 57 58 59 60 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 53 54 55 56 57 58 59 60 61 62 63 64 65 66 67 68 69 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 204 205 206 207 208 209 210 211 212 213 214 215 216 217 218 219 220 221 122 123 124 125 126 127 128 129 130 131 132 133 134 135 136 137 138 139 171 172 173 174 175 176 177 178 179 180 181 182 183 184 185 186 187 140 141 142 143 144 145 146 147 148 149 150 151 152 153 154 155 156 6 7 8 9 10 11 12 13 14
      - generic [ref=e660]:
        - generic [ref=e661]: Request
        - generic [ref=e662]: GET /admin/audit/stats
        - generic [ref=e663]: Headers
        - generic [ref=e664]:
          - generic [ref=e665]:
            - generic [ref=e666] [cursor=pointer]: host
            - code [ref=e669]: localhost
          - generic [ref=e670]:
            - generic [ref=e671] [cursor=pointer]: connection
            - code [ref=e674]: keep-alive
          - generic [ref=e675]:
            - generic [ref=e676] [cursor=pointer]: sec-ch-ua
            - code [ref=e679]: "\"Not=A?Brand\";v=\"99\", \"HeadlessChrome\";v=\"151\", \"Chromium\";v=\"151\""
          - generic [ref=e680]:
            - generic [ref=e681] [cursor=pointer]: sec-ch-ua-mobile
            - code [ref=e684]: "?0"
          - generic [ref=e685]:
            - generic [ref=e686] [cursor=pointer]: sec-ch-ua-platform
            - code [ref=e689]: "\"Windows\""
          - generic [ref=e690]:
            - generic [ref=e691] [cursor=pointer]: upgrade-insecure-requests
            - code [ref=e694]: "1"
          - generic [ref=e695]:
            - generic [ref=e696] [cursor=pointer]: user-agent
            - code [ref=e699]: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.7922.34 Safari/537.36
          - generic [ref=e700]:
            - generic [ref=e701] [cursor=pointer]: accept-language
            - code [ref=e704]: en-US
          - generic [ref=e705]:
            - generic [ref=e706] [cursor=pointer]: accept
            - code [ref=e709]: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
          - generic [ref=e710]:
            - generic [ref=e711] [cursor=pointer]: sec-fetch-site
            - code [ref=e714]: none
          - generic [ref=e715]:
            - generic [ref=e716] [cursor=pointer]: sec-fetch-mode
            - code [ref=e719]: navigate
          - generic [ref=e720]:
            - generic [ref=e721] [cursor=pointer]: sec-fetch-user
            - code [ref=e724]: "?1"
          - generic [ref=e725]:
            - generic [ref=e726] [cursor=pointer]: sec-fetch-dest
            - code [ref=e729]: document
          - generic [ref=e730]:
            - generic [ref=e731] [cursor=pointer]: accept-encoding
            - code [ref=e734]: gzip, deflate, br, zstd
          - generic [ref=e735]:
            - generic [ref=e736] [cursor=pointer]: cookie
            - code [ref=e739]: XSRF-TOKEN=eyJpdiI6Ilpiemd3Z21leURRWXIyNE1UbXR6UEE9PSIsInZhbHVlIjoiN1ltaC9mL2w4ZnhFMUFrTGxXVHQvNlFQQmtLVC9TMSsrU0ppSU0xSFhSTTZRWDdhdGdXandGcGQwNllLbHhOcmhub2RvYWF1K2hVYnd6MmpRUzYwWENGWW1EWHdIcWQ2N25tdHdRZmVWeUVCQUo3ejQrZlVPdlgyaEhydlZNVm0iLCJtYWMiOiJmZjViOTgzZThmNGE0MTU2ZTUyNzhlNTQ5YzQ2MGE3YmJkNjlhMDFjMGU3NDkzMDM3YTRjYzU1NzAwMTc1NWI1IiwidGFnIjoiIn0%3D; easygrox_vellor_local_session=eyJpdiI6IktQUVNnRUpOTkpHU24wdWlOY0JVQ1E9PSIsInZhbHVlIjoiNEpGSlBnYXRaQWo4cjNSTjdDVkVpQ2pKb0RXK2RWLzlLelRUMWRvNENKSGc0SHp2bHQwWmU0cUMvNGdjZVA2YVl4aUdBWTBYbG5FSkwrMDZhdU00MTB3b1hIZTkySXRFR2JwV3o4ekRGSG9LMG85c1ZSZHVMVDhJMmZYWS9FcE0iLCJtYWMiOiJlY2JkYjMzY2JiMDBjMDUxMzYzY2QwNTQ4MWY4YWE0MmIzZWYzNjQwMWZlMWU5M2MxYjNkMDk5YzJlMWIwMzE5IiwidGFnIjoiIn0%3D
        - generic [ref=e740]: Body
        - code [ref=e745]: No body data
      - generic [ref=e746]:
        - generic [ref=e747]: Application
        - generic [ref=e748]: Routing
        - generic [ref=e749]:
          - generic [ref=e750]:
            - generic [ref=e751] [cursor=pointer]: controller
            - code [ref=e754]: App\Http\Controllers\Admin\AuditLogController@stats
          - generic [ref=e755]:
            - generic [ref=e756] [cursor=pointer]: route name
            - code [ref=e759]: admin.audit.stats
          - generic [ref=e760]:
            - generic [ref=e761] [cursor=pointer]: middleware
            - code [ref=e764]: web, auth, verified, 2fa, password.changed, super_admin, user.activity
        - generic [ref=e765]: Database Queries
        - generic [ref=e766]:
          - generic [ref=e767]:
            - generic [ref=e768]:
              - text: mysql
              - generic [ref=e769]: (4.47 ms)
            - code [ref=e772]: "select * from `users` where `id` = 1 and `users`.`deleted_at` is null limit 1"
          - generic [ref=e773]:
            - generic [ref=e774]:
              - text: mysql
              - generic [ref=e775]: (2.62 ms)
            - code [ref=e778]: "select * from `cache` where `key` in ('spatie.permission.cache')"
          - generic [ref=e779]:
            - generic [ref=e780]:
              - text: mysql
              - generic [ref=e781]: (5.36 ms)
            - code [ref=e784]: "select DATE_FORMAT(occurred_at, '%Y-%m-%d %H:00:00') as hour, `severity`, COUNT(*) as count from `audit_logs` where `occurred_at` >= '2026-08-08 12:40:13' group by `hour`, `severity` order by `hour` asc"
          - generic [ref=e785]:
            - generic [ref=e786]:
              - text: mysql
              - generic [ref=e787]: (0.98 ms)
            - code [ref=e790]: "select `ip_address`, COUNT(*) as count from `audit_logs` where `ip_address` is not null and `occurred_at` >= '2026-08-08 12:40:13' group by `ip_address` order by `count` desc limit 10"
          - generic [ref=e791]:
            - generic [ref=e792]:
              - text: mysql
              - generic [ref=e793]: (0.66 ms)
            - code [ref=e796]: "select * from `audit_logs` where `event` = 'auth.failed' and `occurred_at` >= '2026-08-08 12:40:13' order by `occurred_at` desc limit 20"
          - generic [ref=e797]:
            - generic [ref=e798]:
              - text: mysql
              - generic [ref=e799]: (0.46 ms)
            - code [ref=e802]: "select * from `audit_logs` where `severity` = 'critical' and `occurred_at` >= '2026-08-08 12:40:13' order by `occurred_at` desc limit 10"
          - generic [ref=e803]:
            - generic [ref=e804]:
              - text: mysql
              - generic [ref=e805]: (0.97 ms)
            - code [ref=e808]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e809]:
            - generic [ref=e810]:
              - text: mysql
              - generic [ref=e811]: (0.85 ms)
            - code [ref=e814]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e815]:
            - generic [ref=e816]:
              - text: mysql
              - generic [ref=e817]: (1.11 ms)
            - code [ref=e820]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e821]:
            - generic [ref=e822]:
              - text: mysql
              - generic [ref=e823]: (0.46 ms)
            - code [ref=e826]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e827]:
            - generic [ref=e828]:
              - text: mysql
              - generic [ref=e829]: (0.61 ms)
            - code [ref=e832]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e833]:
            - generic [ref=e834]:
              - text: mysql
              - generic [ref=e835]: (0.55 ms)
            - code [ref=e838]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e839]:
            - generic [ref=e840]:
              - text: mysql
              - generic [ref=e841]: (0.87 ms)
            - code [ref=e844]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e845]:
            - generic [ref=e846]:
              - text: mysql
              - generic [ref=e847]: (0.38 ms)
            - code [ref=e850]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e851]:
            - generic [ref=e852]:
              - text: mysql
              - generic [ref=e853]: (0.54 ms)
            - code [ref=e856]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e857]:
            - generic [ref=e858]:
              - text: mysql
              - generic [ref=e859]: (0.57 ms)
            - code [ref=e862]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e863]:
            - generic [ref=e864]:
              - text: mysql
              - generic [ref=e865]: (0.31 ms)
            - code [ref=e868]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e869]:
            - generic [ref=e870]:
              - text: mysql
              - generic [ref=e871]: (0.22 ms)
            - code [ref=e874]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e875]:
            - generic [ref=e876]:
              - text: mysql
              - generic [ref=e877]: (0.32 ms)
            - code [ref=e880]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e881]:
            - generic [ref=e882]:
              - text: mysql
              - generic [ref=e883]: (0.25 ms)
            - code [ref=e886]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e887]:
            - generic [ref=e888]:
              - text: mysql
              - generic [ref=e889]: (0.43 ms)
            - code [ref=e892]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e893]:
            - generic [ref=e894]:
              - text: mysql
              - generic [ref=e895]: (0.27 ms)
            - code [ref=e898]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e899]:
            - generic [ref=e900]:
              - text: mysql
              - generic [ref=e901]: (0.68 ms)
            - code [ref=e904]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e905]:
            - generic [ref=e906]:
              - text: mysql
              - generic [ref=e907]: (0.41 ms)
            - code [ref=e910]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e911]:
            - generic [ref=e912]:
              - text: mysql
              - generic [ref=e913]: (0.77 ms)
            - code [ref=e916]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e917]:
            - generic [ref=e918]:
              - text: mysql
              - generic [ref=e919]: (0.42 ms)
            - code [ref=e922]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e923]:
            - generic [ref=e924]:
              - text: mysql
              - generic [ref=e925]: (0.56 ms)
            - code [ref=e928]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e929]:
            - generic [ref=e930]:
              - text: mysql
              - generic [ref=e931]: (0.41 ms)
            - code [ref=e934]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e935]:
            - generic [ref=e936]:
              - text: mysql
              - generic [ref=e937]: (1.36 ms)
            - code [ref=e940]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e941]:
            - generic [ref=e942]:
              - text: mysql
              - generic [ref=e943]: (0.69 ms)
            - code [ref=e946]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e947]:
            - generic [ref=e948]:
              - text: mysql
              - generic [ref=e949]: (1.83 ms)
            - code [ref=e952]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e953]:
            - generic [ref=e954]:
              - text: mysql
              - generic [ref=e955]: (1.62 ms)
            - code [ref=e958]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e959]:
            - generic [ref=e960]:
              - text: mysql
              - generic [ref=e961]: (0.79 ms)
            - code [ref=e964]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e965]:
            - generic [ref=e966]:
              - text: mysql
              - generic [ref=e967]: (0.33 ms)
            - code [ref=e970]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e971]:
            - generic [ref=e972]:
              - text: mysql
              - generic [ref=e973]: (0.6 ms)
            - code [ref=e976]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e977]:
            - generic [ref=e978]:
              - text: mysql
              - generic [ref=e979]: (0.32 ms)
            - code [ref=e982]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e983]:
            - generic [ref=e984]:
              - text: mysql
              - generic [ref=e985]: (0.38 ms)
            - code [ref=e988]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e989]:
            - generic [ref=e990]:
              - text: mysql
              - generic [ref=e991]: (0.43 ms)
            - code [ref=e994]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e995]:
            - generic [ref=e996]:
              - text: mysql
              - generic [ref=e997]: (0.94 ms)
            - code [ref=e1000]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e1001]:
            - generic [ref=e1002]:
              - text: mysql
              - generic [ref=e1003]: (0.42 ms)
            - code [ref=e1006]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e1007]:
            - generic [ref=e1008]:
              - text: mysql
              - generic [ref=e1009]: (1.18 ms)
            - code [ref=e1012]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e1013]:
            - generic [ref=e1014]:
              - text: mysql
              - generic [ref=e1015]: (0.68 ms)
            - code [ref=e1018]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e1019]:
            - generic [ref=e1020]:
              - text: mysql
              - generic [ref=e1021]: (0.8 ms)
            - code [ref=e1024]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e1025]:
            - generic [ref=e1026]:
              - text: mysql
              - generic [ref=e1027]: (0.31 ms)
            - code [ref=e1030]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
          - generic [ref=e1031]:
            - generic [ref=e1032]:
              - text: mysql
              - generic [ref=e1033]: (1.02 ms)
            - code [ref=e1036]: "select exists(select * from `salons` where `salons`.`owner_id` = 1 and `salons`.`owner_id` is not null and `salons`.`deleted_at` is null) as `exists`"
          - generic [ref=e1037]:
            - generic [ref=e1038]:
              - text: mysql
              - generic [ref=e1039]: (0.36 ms)
            - code [ref=e1042]: "select `salon_id` from `staff` where `user_id` = 1 and `deleted_at` is null limit 1"
```

# Test source

```ts
  1  | import { expect, type Page } from '@playwright/test';
  2  | 
  3  | /** Paths must be relative — leading `/` drops `/vellor/admin`. */
  4  | export function appPath(path: string): string {
  5  |   return path.replace(/^\//, '');
  6  | }
  7  | 
  8  | export async function assertUsablePage(
  9  |   page: Page,
  10 |   path: string,
  11 |   options: {
  12 |     allowLoginRedirect?: boolean;
  13 |     expectUrlIncludes?: string | RegExp;
  14 |     maxStatus?: number;
  15 |     planId?: string;
  16 |   } = {},
  17 | ) {
  18 |   const response = await page.goto(appPath(path), { waitUntil: 'domcontentloaded', timeout: 60_000 });
  19 |   const status = response?.status() ?? 0;
  20 |   const label = options.planId ? `${options.planId} ${path}` : path;
  21 | 
> 22 |   expect(status, `${label} status`).toBeLessThan(options.maxStatus ?? 500);
     |                                     ^ Error: SA-18b admin/audit/stats status
  23 | 
  24 |   const body = (await page.locator('body').innerText().catch(() => '')).slice(0, 4000);
  25 |   expect(body, `${label} no server error`).not.toMatch(/Server Error|Whoops!/i);
  26 |   expect(body.length, `${label} has content`).toBeGreaterThan(20);
  27 | 
  28 |   if (!options.allowLoginRedirect) {
  29 |     expect(page.url(), `${label} authenticated`).not.toMatch(/\/login(\?|$)/);
  30 |   }
  31 |   if (options.expectUrlIncludes) {
  32 |     expect(page.url()).toMatch(options.expectUrlIncludes);
  33 |   }
  34 | 
  35 |   return { status, url: page.url() };
  36 | }
  37 | 
  38 | export const QA = {
  39 |   store: 'ak',
  40 |   tenantEmail: 'ajayjatav439@gmail.com',
  41 |   tenantPassword: 'password',
  42 |   adminEmail: 'admin@velour.app',
  43 |   adminPassword: 'password',
  44 |   salonId: 2,
  45 |   ownerId: 5,
  46 |   ids: {
  47 |     client: 151,
  48 |     staff: 7,
  49 |     service: 90,
  50 |     appointment: 303,
  51 |     inventory: 59,
  52 |     package: 1,
  53 |     marketing: 9,
  54 |     pos: 188,
  55 |   },
  56 |   reviewToken: 'xP4zt5jH1GPVZgM3IwVeyoj6xrl67eCczQhoa4pfykPma6IO',
  57 |   helpSlug: 'getting-started-with-velour',
  58 |   storefrontSlug: 'ak-salon',
  59 | };
  60 | 
  61 | export function storePath(suffix: string) {
  62 |   return `${QA.store}/${suffix.replace(/^\//, '')}`;
  63 | }
  64 | 
```