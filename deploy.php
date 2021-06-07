<?php

namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'my_project');

// Project repository
set('repository', 'git@github.com:NHZEX/nxAdmin.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_files', ['.env']);
set('shared_dirs', ['public/upload']);

// Writable dirs by web server
set('writable_dirs', ['public/upload', 'runtime']);
set('allow_anonymous_stats', false);

set('deploy_path', '~/wwwroot/{{application}}');
set('default_stage', 'production');

set('composer_action', 'install');
$opt = '--verbose --prefer-dist --no-progress --no-interaction --no-dev --no-suggest --no-scripts';
set('composer_options', "{{composer_action}} $opt");

// Hosts
localhost()
    ->stage('production')
    ->roles('test', 'build');

//host('project.com')
//    ->set('deploy_path', '~/wwwroot/{{application}}');

// Tasks

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
