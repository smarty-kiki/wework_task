#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..

ln -fs $ROOT_DIR/project/config/production/nginx/wework_task.conf /etc/nginx/sites-enabled/wework_task
/usr/sbin/service nginx reload

/bin/bash $ROOT_DIR/project/tool/dep_build.sh link
/usr/bin/php $ROOT_DIR/public/cli.php migrate:install
/usr/bin/php $ROOT_DIR/public/cli.php migrate

ln -fs $ROOT_DIR/project/config/production/supervisor/wework_task_queue_worker.conf /etc/supervisor/conf.d/wework_task_queue_worker.conf
/usr/bin/supervisorctl update
/usr/bin/supervisorctl restart wework_task_queue_worker:*

chmod 777 /var/www/wework_task/view/blade
rm -rf /var/www/wework_task/view/blade/*.php
