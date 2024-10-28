FROM ubuntu:latest
LABEL authors="ercanakca"

ENTRYPOINT ["top", "-b"]
