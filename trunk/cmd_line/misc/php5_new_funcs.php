<?php

# http://ca2.php.net/manual/en/migration5.functions.php

pspell_config_dict_dir();
pspell_config_data_dir();

ibase_service_attach();
ibase_service_detach();
ibase_backup();
ibase_maintain_db();
ibase_db_info();
ibase_server_info();

dba_key_split();
time_nanosleep();
headers_list();
php_strip_whitespace();
php_check_syntax();
image_type_to_extension();
stream_socket_sendto();
iconv_mime_decode_headers();
get_declared_interfaces();
sqlite_fetch_column_types();

setrawcookie();
pg_version();
dbase_get_header();
snmp_read_mib();
http_build_query();
ftp_alloc();
array_udiff();
array_udiff_assoc();
array_udiff_uassoc();
array_diff_uassoc();
convert_uuencode();
convert_uudecode();
substr_compare();
pcntl_wait();

strireplace();

*** uniqid();

ldap_sasl_bind();
imap_getacl();
file_put_contents();
proc_nice();
pcntl_getpriority();
idate();
date_sunrise();
date_sunset();
strpbrk();
get_headers();
str_split();
array_walk_recursive();
array_combine();

range(); -- new features








