
-----------------------------------------------------------------------------
-- sf_guard_group
-----------------------------------------------------------------------------

DROP TABLE "sf_guard_group" CASCADE;

DROP SEQUENCE "sf_guard_group_seq";

CREATE SEQUENCE "sf_guard_group_seq";


CREATE TABLE "sf_guard_group"
(
	"id" INTEGER  NOT NULL,
	"name" VARCHAR(255)  NOT NULL,
	"description" TEXT,
	PRIMARY KEY ("id")
);

COMMENT ON TABLE "sf_guard_group" IS '';


SET search_path TO public;
CREATE INDEX "unique_group_name" ON "sf_guard_group" ("name");

-----------------------------------------------------------------------------
-- sf_guard_group_permission
-----------------------------------------------------------------------------

DROP TABLE "sf_guard_group_permission" CASCADE;


CREATE TABLE "sf_guard_group_permission"
(
	"group_id" INTEGER  NOT NULL,
	"permission_id" INTEGER  NOT NULL,
	PRIMARY KEY ("group_id","permission_id")
);

COMMENT ON TABLE "sf_guard_group_permission" IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- sf_guard_permission
-----------------------------------------------------------------------------

DROP TABLE "sf_guard_permission" CASCADE;

DROP SEQUENCE "sf_guard_permission_seq";

CREATE SEQUENCE "sf_guard_permission_seq";


CREATE TABLE "sf_guard_permission"
(
	"id" INTEGER  NOT NULL,
	"name" VARCHAR(255)  NOT NULL,
	"description" TEXT,
	PRIMARY KEY ("id"),
	CONSTRAINT "unique_perm_name" UNIQUE ("name")
);

COMMENT ON TABLE "sf_guard_permission" IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- sf_guard_user
-----------------------------------------------------------------------------

DROP TABLE "sf_guard_user" CASCADE;

DROP SEQUENCE "sf_guard_user_seq";

CREATE SEQUENCE "sf_guard_user_seq";


CREATE TABLE "sf_guard_user"
(
	"id" INTEGER  NOT NULL,
	"username" VARCHAR(128)  NOT NULL,
	"algorithm" VARCHAR(128) default '\asha1\a' NOT NULL,
	"salt" VARCHAR(128)  NOT NULL,
	"password" VARCHAR(128)  NOT NULL,
	"created_at" TIMESTAMP,
	"last_login" TIMESTAMP,
	"is_active" BOOLEAN default 't' NOT NULL,
	"is_super_admin" BOOLEAN default 'f' NOT NULL,
	PRIMARY KEY ("id")
);

COMMENT ON TABLE "sf_guard_user" IS '';


SET search_path TO public;
CREATE INDEX "unique_username" ON "sf_guard_user" ("username");

-----------------------------------------------------------------------------
-- sf_guard_user_permission
-----------------------------------------------------------------------------

DROP TABLE "sf_guard_user_permission" CASCADE;


CREATE TABLE "sf_guard_user_permission"
(
	"user_id" INTEGER  NOT NULL,
	"permission_id" INTEGER  NOT NULL,
	PRIMARY KEY ("user_id","permission_id")
);

COMMENT ON TABLE "sf_guard_user_permission" IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- sf_guard_user_group
-----------------------------------------------------------------------------

DROP TABLE "sf_guard_user_group" CASCADE;


CREATE TABLE "sf_guard_user_group"
(
	"group_id" INTEGER  NOT NULL,
	"user_id" INTEGER  NOT NULL,
	PRIMARY KEY ("group_id","user_id")
);

COMMENT ON TABLE "sf_guard_user_group" IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- sf_guard_remember_key
-----------------------------------------------------------------------------

DROP TABLE "sf_guard_remember_key" CASCADE;


CREATE TABLE "sf_guard_remember_key"
(
	"user_id" INTEGER  NOT NULL,
	"remember_key" VARCHAR(32),
	"ip_address" VARCHAR(15)  NOT NULL,
	"created_at" TIMESTAMP,
	PRIMARY KEY ("user_id","ip_address")
);

COMMENT ON TABLE "sf_guard_remember_key" IS '';


SET search_path TO public;
ALTER TABLE "sf_guard_group_permission" ADD CONSTRAINT "sf_guard_group_permission_FK_1" FOREIGN KEY ("group_id") REFERENCES "sf_guard_group" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_group_permission" ADD CONSTRAINT "sf_guard_group_permission_FK_2" FOREIGN KEY ("permission_id") REFERENCES "sf_guard_permission" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_user_permission" ADD CONSTRAINT "sf_guard_user_permission_FK_1" FOREIGN KEY ("user_id") REFERENCES "sf_guard_user" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_user_permission" ADD CONSTRAINT "sf_guard_user_permission_FK_2" FOREIGN KEY ("permission_id") REFERENCES "sf_guard_permission" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_user_group" ADD CONSTRAINT "sf_guard_user_group_FK_1" FOREIGN KEY ("user_id") REFERENCES "sf_guard_user" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_user_group" ADD CONSTRAINT "sf_guard_user_group_FK_2" FOREIGN KEY ("group_id") REFERENCES "sf_guard_group" ("id") ON DELETE CASCADE;

ALTER TABLE "sf_guard_remember_key" ADD CONSTRAINT "sf_guard_remember_key_FK_1" FOREIGN KEY ("user_id") REFERENCES "sf_guard_user" ("id") ON DELETE CASCADE;
