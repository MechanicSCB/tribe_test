### deploy with Docker
```
git clone https://github.com/MechanicSCB/tribe_test <project-name>
```
```
cd <project-name>
```
```
composer update
```
```
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```
```
sail up -d
```
```
sail artisan key:generate
```
```
sail artisan migrate:fresh --seed
```

(optional) run php stan analyse
```
vendor/bin/phpstan analyse -l 7 tests app/Http/Controllers/ResultController.php
```

(optional) generate open api json (/storage/api-docs/api-docs.json)
```
sail artisan l5-swagger:generate
```


- Visit `http://localhost/api/top`
