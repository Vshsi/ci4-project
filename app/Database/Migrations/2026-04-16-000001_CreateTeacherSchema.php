<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinalTeacherSchema extends Migration
{
    public function up()
    {
        // --- 0. WIPE OLD SCHEMA ---
        $this->db->query('DROP TABLE IF EXISTS support_replies CASCADE');
        $this->db->query('DROP TABLE IF EXISTS support_tickets CASCADE');
        $this->db->query('DROP TABLE IF EXISTS public.auth_identities CASCADE');
        $this->db->query('DROP TABLE IF EXISTS public.auth_logins CASCADE');
        $this->db->query('DROP TABLE IF EXISTS public.users CASCADE');
        
        $this->db->query('DROP SEQUENCE IF EXISTS users_id_seq CASCADE');
        $this->db->query('DROP SEQUENCE IF EXISTS auth_logins_id_seq CASCADE');
        $this->db->query('DROP SEQUENCE IF EXISTS auth_identities_id_seq CASCADE');

        $this->db->query('DROP TYPE IF EXISTS ticket_status CASCADE');
        $this->db->query('DROP TYPE IF EXISTS ticket_priority CASCADE');
        $this->db->query('DROP TYPE IF EXISTS communication_medium_type CASCADE');

        // 1. CREATE SEQUENCES (Required for 'nextval' used in teacher schema)
        $this->db->query("CREATE SEQUENCE IF NOT EXISTS users_id_seq INCREMENT 1 START 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1");
        $this->db->query("CREATE SEQUENCE IF NOT EXISTS auth_logins_id_seq INCREMENT 1 START 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1");
        $this->db->query("CREATE SEQUENCE IF NOT EXISTS auth_identities_id_seq INCREMENT 1 START 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1");

        // 2. TABLE: public.users (EXACT COPY FROM schema.txt)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS public.users
            (
                id integer NOT NULL DEFAULT nextval('users_id_seq'::regclass),
                username character varying(30) COLLATE pg_catalog.\"default\",
                status character varying(10) COLLATE pg_catalog.\"default\",
                status_message character varying(255) COLLATE pg_catalog.\"default\",
                active smallint NOT NULL DEFAULT 0,
                last_active timestamp without time zone,
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                first_name character varying(60) COLLATE pg_catalog.\"default\",
                last_name character varying(50) COLLATE pg_catalog.\"default\",
                email_id character varying(100) COLLATE pg_catalog.\"default\",
                mobile character varying(10) COLLATE pg_catalog.\"default\",
                designation_id character varying(50) COLLATE pg_catalog.\"default\",
                about character varying(200) COLLATE pg_catalog.\"default\",
                profile_image character varying(255) COLLATE pg_catalog.\"default\",
                created_by integer,
                updated_by integer,
                permanent_address character varying(255) COLLATE pg_catalog.\"default\",
                present_address character varying(255) COLLATE pg_catalog.\"default\",
                permanent_landmark character varying(100) COLLATE pg_catalog.\"default\",
                permanent_state integer DEFAULT 0,
                permanent_city integer,
                permanent_pincode character varying(6) COLLATE pg_catalog.\"default\",
                present_landmark character varying(100) COLLATE pg_catalog.\"default\",
                present_state integer,
                present_city integer,
                present_pincode character varying(6) COLLATE pg_catalog.\"default\",
                deleted_at timestamp with time zone,
                deleted_by integer,
                paswd character varying(255) COLLATE pg_catalog.\"default\",
                user_type_id integer,
                school_id integer,
                email character varying(255) COLLATE pg_catalog.\"default\",
                CONSTRAINT pk_users PRIMARY KEY (id),
                CONSTRAINT users_username UNIQUE (username)
            )
            TABLESPACE pg_default;
        ");
        $this->db->query("ALTER TABLE IF EXISTS public.users OWNER to postgres;");

        // 3. TABLE: public.auth_logins (EXACT COPY FROM schema.txt)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS public.auth_logins
            (
                id integer NOT NULL DEFAULT nextval('auth_logins_id_seq'::regclass),
                ip_address character varying(255) COLLATE pg_catalog.\"default\" NOT NULL,
                user_agent character varying(255) COLLATE pg_catalog.\"default\",
                id_type character varying(255) COLLATE pg_catalog.\"default\" NOT NULL,
                identifier character varying(255) COLLATE pg_catalog.\"default\" NOT NULL,
                user_id integer,
                date timestamp without time zone NOT NULL,
                success smallint NOT NULL,
                CONSTRAINT pk_auth_logins PRIMARY KEY (id)
            )
            TABLESPACE pg_default;
        ");
        $this->db->query("ALTER TABLE IF EXISTS public.auth_logins OWNER to postgres;");
        
        $this->db->query("
            CREATE INDEX IF NOT EXISTS auth_logins_id_type_identifier
                ON public.auth_logins USING btree
                (id_type COLLATE pg_catalog.\"default\" ASC NULLS LAST, identifier COLLATE pg_catalog.\"default\" ASC NULLS LAST)
                WITH (fillfactor=100, deduplicate_items=True)
                TABLESPACE pg_default;
        ");
        $this->db->query("
            CREATE INDEX IF NOT EXISTS auth_logins_user_id
                ON public.auth_logins USING btree
                (user_id ASC NULLS LAST)
                WITH (fillfactor=100, deduplicate_items=True)
                TABLESPACE pg_default;
        ");

        // 4. TABLE: public.auth_identities (EXACT COPY FROM schema.txt)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS public.auth_identities
            (
                id integer NOT NULL DEFAULT nextval('auth_identities_id_seq'::regclass),
                user_id integer NOT NULL,
                type character varying(255) COLLATE pg_catalog.\"default\" NOT NULL,
                name character varying(255) COLLATE pg_catalog.\"default\",
                secret character varying(255) COLLATE pg_catalog.\"default\" NOT NULL,
                secret2 character varying(255) COLLATE pg_catalog.\"default\",
                expires timestamp without time zone,
                extra text COLLATE pg_catalog.\"default\",
                force_reset smallint NOT NULL DEFAULT 0,
                last_used_at timestamp without time zone,
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                CONSTRAINT pk_auth_identities PRIMARY KEY (id),
                CONSTRAINT auth_identities_type_secret UNIQUE (type, secret),
                CONSTRAINT auth_identities_user_id_foreign FOREIGN KEY (user_id)
                    REFERENCES public.users (id) MATCH SIMPLE
                    ON UPDATE NO ACTION
                    ON DELETE CASCADE
            )
            TABLESPACE pg_default;
        ");
        $this->db->query("ALTER TABLE IF EXISTS public.auth_identities OWNER to postgres;");
        
        $this->db->query("
            CREATE INDEX IF NOT EXISTS auth_identities_user_id
                ON public.auth_identities USING btree
                (user_id ASC NULLS LAST)
                WITH (fillfactor=100, deduplicate_items=True)
                TABLESPACE pg_default;
        ");

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS public.auth_identities');
        $this->db->query('DROP TABLE IF EXISTS public.auth_logins');
        $this->db->query('DROP TABLE IF EXISTS public.users');
        $this->db->query('DROP SEQUENCE IF EXISTS users_id_seq');
        $this->db->query('DROP SEQUENCE IF EXISTS auth_logins_id_seq');
        $this->db->query('DROP SEQUENCE IF EXISTS auth_identities_id_seq');
    }
}
