FROM debian:jessie

RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y apt-utils curl zsh ssh git supervisor sudo php5 php5-curl php5-intl php5-mysql php5-xdebug \
                       php5-dev git less vim-tiny apg nano

COPY config/sudoers /etc/sudoers
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN useradd -ms /bin/bash paraunit
RUN usermod -aG sudo paraunit
USER paraunit

WORKDIR /home/paraunit/projects

#COMPOSER
RUN sudo curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

#Zsh minimal installation
RUN git clone --depth=1 git://github.com/robbyrussell/oh-my-zsh.git  ~/.oh-my-zsh
RUN cp ~/.oh-my-zsh/templates/zshrc.zsh-template ~/.zshrc
RUN sudo chsh -s /bin/zsh

WORKDIR /tmp/

RUN git clone --depth=1 https://github.com/symfony/symfony.git symfony

WORKDIR /tmp/symfony

RUN composer require 'facile-it/paraunit'

VOLUME ["/tmp/symfony/vendor/facile-it/paraunit/"]

CMD sudo /usr/bin/supervisord
