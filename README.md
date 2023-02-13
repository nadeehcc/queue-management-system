==Installation guide
1. Install DB server
2. Install PHP
3. Install Larvael composer
4. Deploy system
	Copy source code
	Run following command on the source code folder
composer install
php artisan serve
5. Configure database and tables
	Create a database on the db server
	Modify .env file in Laravel project to point db server and the db and credentials 
Run migrations (create table)
6. Create Users, Queues (or resource persons such as Doctors), Rooms and Sessions
	Refer User manual
