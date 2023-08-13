<?php

use Hafo\DI\Container;

return [

    'db.migrations' => function (Container $c) {
        return [
            'first_migration_test' => [
                'SELECT 1',
            ],
            'firebase_push_token' => [
                'create table firebase_push_token
(
	id int auto_increment,
	user int(8) not null,
	token text not null,
	constraint firebase_push_token_pk
		primary key (id),
	constraint firebase_push_token_system_user_id_fk
		foreign key (user) references system_user (id)
			on update cascade on delete cascade
);
',
            ],
            'vip_level' => [
                'alter table system_user change is_vip vip_level int(1) default 0 not null;',
                'alter table vcd_application change is_vip vip_level int(1) unsigned default 0 not null;',
            ],
            'cron_task_category' => [
                'alter table cron_task
	add category varchar(16) default \'\' not null after id;',
'create index cron_task_idx
	on cron_task (executed_at, locked_at, planned_to, category);',
            ],
            'homepage_config' => [
                'create table vcd_homepage_config
(
	enabled_sections text not null
);
',
                'insert into vcd_homepage_config (enabled_sections) VALUES ("whyUs,reviews,games,fairytales,nextEvent,subscribe,archiveEvents")',
            ],
            'vcd_event_image' => [
                'create table vcd_event_image
(
	id int auto_increment,
	event int(8) unsigned not null,
	thumbnail varchar(255) not null,
	image varchar(255) not null,
	position int default 0 not null,
	constraint vcd_event_image_pk
		primary key (id),
	constraint vcd_event_image_vcd_event_id_fk
		foreign key (event) references vcd_event (id)
			on update cascade on delete cascade
);

',

                'alter table vcd_event_image
	add thumb_w int(4) not null;
',
                '
alter table vcd_event_image
	add thumb_h int(4) null;

',
                'alter table vcd_event_image drop column thumbnail;',
                'alter table vcd_event_image change image name varchar(255) not null;',
            ],

            'websiteconfig' => [
                'alter table system_website
	add facebook_link varchar(512) default NULL null;
',
                '
alter table system_website
	add instagram_link varchar(512) default NULL null;
',
                '
alter table system_website
	add pinterest_link varchar(512) default NULL null;
',
                '
alter table system_website
	add address varchar(128) not null;

',
                'alter table system_website
	add terms_and_conditions varchar(256) not null;
',
                '
alter table system_website
	add gdpr varchar(256) not null;
',
                '
alter table system_website
	add rules varchar(256) not null;',
                'alter table system_website
	add contact_person varchar(64) not null;
',
                '
alter table system_website
	add ico varchar(64) not null;
',
                'alter table system_website
	add bank_name varchar(64) not null;
',
                'alter table system_website
	add org_description varchar(256) not null;
',
            ],
            'system_flash_message' => [
               ' create table system_flash_message
        (
            id int auto_increment,
	hash varchar(32) not null,
	type varchar(16) default \'info\' not null,
	created_at datetime default now() not null,
	read_at datetime default null null,
	constraint system_flash_message_pk
		primary key (id)
);',

'create index system_flash_message_hash_read_at_index
	on system_flash_message (hash, read_at);',

'create unique index system_flash_message_hash_uindex
	on system_flash_message (hash);',
                'alter table system_flash_message
	add message varchar(512) not null after type;
',
            ],
            'vcd_menu' => [
                'create table vcd_menu
(
	id int(8) auto_increment,
	`key` varchar(16) not null,
	name varchar(128) not null,
	constraint vcd_menu_pk
		primary key (id)
);
',
                '
create unique index vcd_menu_key_uindex
	on vcd_menu (`key`);

',
                'INSERT INTO vcd_menu (`key`, name) VALUES (\'top\', \'Horní menu\');',
                'INSERT INTO vcd_menu (`key`, name) VALUES (\'main\', \'Hlavní menu\');',
                'INSERT INTO vcd_menu (`key`, name) VALUES (\'mobile\', \'Mobilní menu\');',
                'INSERT INTO vcd_menu (`key`, name) VALUES (\'footer\', \'Menu v patičce\');',

                'create table vcd_menu_item
        (
            id int auto_increment,
	menu int(8) not null,
	position int default 0 not null,
	url varchar(256) not null,
	text varchar(256) not null,
	constraint vcd_menu_item_pk
		primary key (id),
	constraint vcd_menu_item_vcd_menu_id_fk
		foreign key (menu) references vcd_menu (id)
);',

                'create index vcd_menu_item_position_index
	on vcd_menu_item (position);',
                'alter table vcd_menu_item
	add visible int(1) default 1 not null;',
                'create index vcd_menu_item_position_visible_index
	on vcd_menu_item (position, visible);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (1, 0, \'#\', \'E-booky\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (1, 1, \'#\', \'Naše hry\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (1, 2, \'#\', \'Vedoucí a lektoři\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (1, 3, \'#\', \'Proč k nám\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (2, 0, \'/\', \'Domů\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (2, 1, \'/tabory\', \'Tábory\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (2, 2, \'/vylety\', \'Výlety\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (2, 3, \'#\', \'Fotky\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (2, 4, \'#\', \'Recenze\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (2, 5, \'#\', \'Archiv\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (2, 6, \'/kontakty\', \'Kontakty\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 0, \'/\', \'Domů\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 1, \'/tabory\', \'Tábory\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 2, \'/vylety\', \'Výlety\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 3, \'#\', \'Fotky\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 4, \'#\', \'Proč k nám\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 5, \'#\', \'Archiv\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 6, \'#\', \'E-booky\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 7, \'#\', \'Naše hry\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 8, \'#\', \'Vedoucí a lektoři\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 9, \'#\', \'Recenze\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (3, 10, \'/kontakty\', \'Kontakty\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (4, 0, \'#\', \'Přidej se k nám\', 1);',
                'INSERT INTO vcd_menu_item (menu, position, url, text, visible) VALUES (4, 1, \'#\', \'Ztráty a nálezy\', 1);',
            ],
            'vcd_game' => [
                'set foreign_key_checks = 0;',
                'drop table vcd_game_tag;',
                'drop table vcd_game;',
                'set foreign_key_checks = 1;',

                'create table vcd_game
        (
            id int(8) auto_increment,
	name varchar(255) not null,
	slug varchar(255) not null,
	visible int(1) default 1 not null,
	position int(8) default 0 not null,
	visible_on_homepage int(1) default 1 not null,
	banner_small varchar(255) default null null,
	banner_large varchar(255) default null null,
	description_short text not null,
	description_long text null,
	constraint vcd_game_pk
		primary key (id)
);',
                'create index vcd_game_position_index
	on vcd_game (position);',
            ],
            'menu_item.external' => [
                'alter table vcd_menu_item
	add is_external int(1) default 0 not null;
',
            ],
            'website.heading' => [
                'alter table system_website
	add heading varchar(255) not null after title;
',
                'update system_website set heading = \'Letní a víkendové tábory pro děti!\', slogan = \'Dopřejte svým dětem zábavu během celého roku!\'',
            ],
            'web_code.indexes' => [
                'create index vcd_web_code_position_index
	on vcd_web_code (position);',
                'create index vcd_web_code_visible_index
	on vcd_web_code (visible);',
            ],
            'vcd_event.subheading' => [
                'alter table vcd_event
	add subheading varchar(255) default null null after name;',
            ],
            'vcd_event.sidebar_html' => [
                'alter table vcd_event
	add sidebar_html text null;',
            ],
            'vcd_application.internal_notes' => [
                'alter table vcd_application
	add internal_notes text null;',
            ],
            'vcd.invoice.notes' => [
                'alter table vcd_invoice
	add notes text default null null;',
            ],
            'system_website.iban' => [
                'alter table system_website
	add iban varchar(64) default \'\' not null;',
            ],
            'vcd_application.invoice_notes' => [
                'alter table vcd_application
	add invoice_notes text null after invoice_zip;',
                'alter table system_user
	add invoice_notes text null after invoice_zip;',
                'alter table vcd_application
	add has_invoice tinyint(1) default 0 not null;',
                'create index vcd_application_has_invoice_index
	on vcd_application (has_invoice);',
                'alter table vcd_invoice
	add custom_file varchar(255) default null null;',
            ],
            'vcd_event.schema_org' => [
                'alter table vcd_event
	add schema_location_name varchar(128) null;',
                'alter table vcd_event
	add schema_location_address_postal_code varchar(6) null;',
                'alter table vcd_event
	add schema_location_address_region varchar(32) null;',
                'alter table vcd_event
	add schema_location_address_locality varchar(128) null;',
            ],
        ];
    },

    \Hafo\DatabaseMigration\Migrator::class => function (Container $c) {
        return $c->get(\Hafo\DatabaseMigration\Migrator\DefaultMigrator::class);
    },

    \Hafo\DatabaseMigration\Migrator\DefaultMigrator::class => function (Container $c) {
        return new \Hafo\DatabaseMigration\Migrator\DefaultMigrator(
            $c->get('db.fullAccess'),
            $c->get('db.migrations')
        );
    },

];
