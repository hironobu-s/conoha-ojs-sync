NAME=conoha-ojs-sync

all: 
	mkdir -p $(NAME)
	cp -r conoha-ojs-sync.php script style tpl vendor $(NAME)
	zip -vr conoha-ojs-sync.zip $(NAME)
clean:
	rm -rf $(NAME)
