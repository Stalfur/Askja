CREATE TABLE "public"."comment" (
"id" int4 NOT NULL,
"table" varchar(50) COLLATE "default",
"item_id" int4,
"user" int4,
"datestamp" timestamp(0),
"comment" text COLLATE "default",
CONSTRAINT "comment_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."continents" (
"id" int4 NOT NULL,
"name" varchar(100) COLLATE "default",
CONSTRAINT "continents_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."countries" (
"id" int4 NOT NULL,
"osm_id" varchar(20) COLLATE "default",
"name" varchar(200) COLLATE "default",
"name_en" varchar(200) COLLATE "default",
"int_name" varchar(200) COLLATE "default",
"continent" int4,
"iso3166" varchar(5) COLLATE "default",
"lat" varchar(20) COLLATE "default",
"lon" varchar(20) COLLATE "default",
CONSTRAINT "countries_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."export" (
"k" varchar(255) COLLATE "default",
"v" varchar(255) COLLATE "default"
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."import_test" (
"id" int4,
"lat" varchar(20) COLLATE "default",
"lon" varchar(20) COLLATE "default",
"name" varchar(200) COLLATE "default",
"place" varchar(50) COLLATE "default",
"wikipedia" varchar(200) COLLATE "default",
"population" int4,
"is_in" varchar(100) COLLATE "default"
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."item" (
"id" int4 NOT NULL,
"box" int4 NOT NULL,
"type" varchar(60) COLLATE "default",
"name" varchar(100) COLLATE "default",
"category" varchar(100) COLLATE "default",
"filter" varchar(100) COLLATE "default",
"longitude" varchar(20) COLLATE "default",
"latitude" varchar(20) COLLATE "default",
"zoom" int4 NOT NULL,
"osm_type" varchar(10) COLLATE "default",
"osm_id" varchar(20) COLLATE "default",
CONSTRAINT "item_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
COMMENT ON COLUMN "public"."item"."type" IS 'town, village';
COMMENT ON COLUMN "public"."item"."osm_type" IS 'node, way';
CREATE TABLE "public"."log_actions" (
"id" int4 NOT NULL,
"osm_user" varchar(255) COLLATE "default" NOT NULL,
"the_datetime" timestamp(0) NOT NULL,
"item_table" varchar(50) COLLATE "default" NOT NULL,
"item_id" int4 NOT NULL,
"item_field" varchar(50) COLLATE "default" NOT NULL,
"item_value" varchar(50) COLLATE "default",
"item_query" text COLLATE "default" NOT NULL,
CONSTRAINT "log_actions_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."log_users" (
"aid" int4 NOT NULL,
"osm_user" varchar(255) COLLATE "default",
"osm_id" int4,
"remote_ip" varchar(50) COLLATE "default",
"the_datetime" timestamp(0),
CONSTRAINT "log_users_pkey" PRIMARY KEY ("aid")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."marker_colors" (
"country" varchar(50) COLLATE "default" NOT NULL,
"district" varchar(50) COLLATE "default" NOT NULL,
"marker" varchar(20) COLLATE "default" NOT NULL,
CONSTRAINT "marker_colors_pkey" PRIMARY KEY ("country", "district")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."nodes" (
"id" varchar(255) COLLATE "default",
"version" varchar(255) COLLATE "default",
"timestamp" varchar(255) COLLATE "default",
"uid" varchar(255) COLLATE "default",
"user" varchar(255) COLLATE "default",
"changeset" varchar(255) COLLATE "default",
"lat" varchar(255) COLLATE "default",
"lon" varchar(255) COLLATE "default",
"tag" varchar(255) COLLATE "default"
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."nodes2" (
"id" varchar(255) COLLATE "default",
"version" varchar(255) COLLATE "default",
"timestamp" varchar(255) COLLATE "default",
"uid" varchar(255) COLLATE "default",
"user" varchar(255) COLLATE "default",
"changeset" varchar(255) COLLATE "default",
"lat" varchar(255) COLLATE "default",
"lon" varchar(255) COLLATE "default",
"tag" varchar(255) COLLATE "default"
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."nominatim_rules" (
"id" int4 NOT NULL,
"id_country" int4 NOT NULL,
"rule_type" varchar(20) COLLATE "default",
"xmlfield" varchar(100) COLLATE "default",
"datafield" varchar(100) COLLATE "default",
"priority" int4 NOT NULL,
"old_value" varchar(100) COLLATE "default",
"new_value" varchar(100) COLLATE "default",
CONSTRAINT "nominatim_rules_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."nominatim_xml" (
"id_settlement" int4 NOT NULL,
"created" timestamp(0),
"nominatim" text COLLATE "default",
CONSTRAINT "nominatim_xml_pkey" PRIMARY KEY ("id_settlement")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."places" (
"lat" varchar(255) COLLATE "default",
"lon" varchar(255) COLLATE "default",
"name" varchar(255) COLLATE "default",
"place" varchar(255) COLLATE "default",
"district" varchar(255) COLLATE "default"
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."poi" (
"id" text COLLATE "default" NOT NULL,
"country" varchar(50) COLLATE "default",
"latitude" text COLLATE "default",
"longitude" text COLLATE "default",
"osm_key" text COLLATE "default",
"osm_value" text COLLATE "default",
"name" text COLLATE "default",
CONSTRAINT "poi_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."postnumer" (
"postnumer" varchar(4) COLLATE "default",
"stadur" varchar(255) COLLATE "default",
"tegund" varchar(255) COLLATE "default",
"svaedi" varchar(255) COLLATE "default",
"heimilisfang" varchar(255) COLLATE "default"
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."settlement_areas" (
"id" int4 NOT NULL,
"id_settlement" int4 NOT NULL,
"area_name" varchar(100) COLLATE "default",
"network" int4,
"streets" int4,
"buildings" int4,
"imagery" int4,
"addresses" int4,
"amenities" int4,
"paths" int4,
"mapillary" int4,
CONSTRAINT "settlement_areas_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."settlements" (
"id" int4 NOT NULL,
"id_country" int4 NOT NULL,
"osm_id" varchar(20) COLLATE "default",
"name" varchar(200) COLLATE "default",
"name_en" varchar(200) COLLATE "default",
"lat" varchar(20) COLLATE "default",
"lon" varchar(20) COLLATE "default",
"region" varchar(200) COLLATE "default",
"subregion" varchar(200) COLLATE "default",
"place" varchar(30) COLLATE "default",
"capital" varchar(20) COLLATE "default",
"wikidata" varchar(20) COLLATE "default",
"wikipedia" varchar(200) COLLATE "default",
CONSTRAINT "settlements_pkey" PRIMARY KEY ("id", "id_country")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."settlements2" (
"id" int4 NOT NULL,
"local_id" varchar(20) COLLATE "default" NOT NULL,
"country" varchar(50) COLLATE "default",
"name" varchar(255) COLLATE "default",
"population" numeric(10),
"subdistrict" varchar(255) COLLATE "default",
"district" varchar(255) COLLATE "default",
"osm_link" varchar(255) COLLATE "default",
"latitude" float8,
"longitude" float8,
"place" varchar(10) COLLATE "default",
CONSTRAINT "settlements2_pkey" PRIMARY KEY ("id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."user_preferences" (
"osm_id" int4 NOT NULL,
"use_josm" int4 NOT NULL,
CONSTRAINT "user_preferences_pkey" PRIMARY KEY ("osm_id")
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."way_checks" (
"way" varchar(255) COLLATE "default",
"lat" varchar(255) COLLATE "default",
"lon" varchar(255) COLLATE "default",
"status" int4 NOT NULL
)
WITH (OIDS=FALSE);
COMMENT ON COLUMN "public"."way_checks"."status" IS '2=unknown, 1=fine, 0=deleted';
CREATE TABLE "public"."way_node" (
"way" int4 NOT NULL,
"node" int4 NOT NULL
)
WITH (OIDS=FALSE);
CREATE TABLE "public"."ways" (
"id" varchar(255) COLLATE "default",
"version" varchar(255) COLLATE "default",
"timestamp" varchar(255) COLLATE "default",
"uid" varchar(255) COLLATE "default",
"user" varchar(255) COLLATE "default",
"changeset" varchar(255) COLLATE "default",
"nd" varchar(255) COLLATE "default",
"tag" varchar(255) COLLATE "default"
)
WITH (OIDS=FALSE);
CREATE INDEX "idxids" ON "public"."nodes2" USING btree ("id", "lat", "lon");