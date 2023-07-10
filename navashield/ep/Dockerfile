FROM ubuntu:22.04

RUN apt-get update \
 && apt-get install -y socat libssl-dev zlib1g libcurl4 libseccomp2

RUN useradd ctf

WORKDIR /service

RUN mkdir ./mailbox && chmod 777 ./mailbox

COPY ./start.sh .
RUN chmod 555 ./start.sh

COPY ./mail .
RUN chmod 555 ./mail

COPY ./scanner .
RUN chmod 555 ./scanner

CMD ["./start.sh"]
