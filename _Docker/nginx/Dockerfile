FROM centos:6
MAINTAINER Centos6

RUN yum -y update

RUN rpm -ivh http://nginx.org/packages/centos/6/noarch/RPMS/nginx-release-centos-6-0.el6.ngx.noarch.rpm

RUN yum install nginx -y

WORKDIR /home

CMD ["/usr/sbin/nginx", "-g", "daemon off;"]
