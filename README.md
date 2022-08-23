
### Configuration PostgreSQL servers

```SQL
-- Shard 1
CREATE TABLE books (
   id bigserial not null,
   category_id int not null,
   CONSTRAINT category_id_check CHECK (category_id = 1),
   title character varying not null
)

-- Shard 2
CREATE TABLE books (
   id bigserial not null,
   category_id int not null,
   CONSTRAINT category_id_check CHECK (category_id = 2),
   title character varying not null
)
```

#### Server and mapping

```SQL
CREATE
    EXTENSION postgres_fdw

CREATE
    SERVER books_1_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS ( host 'postgres-b1', port '5432', dbname 'db' )

CREATE
    SERVER books_2_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS ( host 'postgres-b2', port '5432', dbname 'db' )

CREATE
    USER MAPPING FOR CURRENT_USER 
    SERVER books_1_server
    OPTIONS ( user 'user', password 'mypass' )

CREATE
    USER MAPPING FOR CURRENT_USER 
    SERVER books_2_server
    OPTIONS ( user 'user', password 'mypass' )
```

#### Foreign table

```SQL
CREATE 
    FOREIGN TABLE books_1 (
        id bigserial not null,
        category_id int not null,
        title character varying not null
    )
    SERVER books_1_server
    OPTIONS (schema_name 'public', table_name 'books')

CREATE
FOREIGN TABLE books_2 (
        id bigserial not null,
        category_id int not null,
        title character varying not null
    )
    SERVER books_2_server
    OPTIONS (schema_name 'public', table_name 'books')
```

#### View and rules

```SQL
CREATE VIEW books AS 
    SELECT * FROM books_1
    UNION ALL 
    SELECT * FROM books_2
    
CREATE 
    RULE books_insert AS ON INSERT TO books
    DO INSTEAD NOTHING;
    
CREATE
    RULE books_update AS ON UPDATE TO books
    DO INSTEAD NOTHING;

CREATE
    RULE books_delete AS ON DELETE TO books
    DO INSTEAD NOTHING; 
    
CREATE 
    RULE books_insert_to_1 AS ON INSERT TO books
    WHERE (category_id = 1)
    DO INSTEAD INSERT INTO books_1 VALUES (NEW.*)
    
CREATE
    RULE books_insert_to_2 AS ON INSERT TO books
    WHERE (category_id = 2)
    DO INSTEAD INSERT INTO books_2 VALUES (NEW.*)   
```