## Что делал во время выполнения.
Арендовал сервер.  
Установил на машину Nginx + Docker  
### Настройка NGINX
В NGINX добавил виртуальный хост со следующей конфигурацией:
```nginx
upstream container_nginx {
    server localhost:8081;
    server localhost:8082 backup;
}

server {
    listen 80;
    server_name image-api.hu;

    location / {
        proxy_pass http://container_nginx;
        proxy_set_header Host image-api.hu;
    }
}
```
Это позволяет:
+ не изменять конфигурацию проектов, у которых есть свои контейнеры с nginx;
+ закрыть порты помимо 80го;
+ избавиться от downtim'а;
+ использовать один сервер для нескольких проектов. 

### Скрипты деплоя в .github/workflows 
main.yml - деплой последней версии при push и mr в main ветку  

roll-back.yml - отключение последней версии на сервере и направление трафика в предыдущую версию  

start-consumer.yml - ручной запуск демона php в контейнере (на всякий случай)  