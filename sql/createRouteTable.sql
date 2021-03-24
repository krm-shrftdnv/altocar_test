create table altocar_test.route
(
    id bigint auto_increment,
    start_latitude float not null,
    start_longitude float not null,
    end_latitude float not null,
    end_longitude float not null,
    distance float not null,
    constraint route_pk
        primary key (id)
);