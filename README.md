# popular-php-repos
A list of popular PHP repos.

## Architecture
Given the scale of the project, I thought it'd be a little overkill to use a framework or involve third-party libraries, So I've kept it pretty simple. Everything's self-contained in a single file to make it ultra-portable and easily-installable.

## Installation
1. Import the table (`popular_php_repos.sql`) into your database: `mysql -u USERNAME -p DATABASE < popular_php_repos.sql`
2. Edit line 2 of `index.php` to use your database credentials
3. Visit `index.php` in a browser. If everything's configured correctly, you should see a button to "Import Repositories". Click it.