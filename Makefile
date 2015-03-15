NAME=conoha-ojs-sync

all: 
	mkdir -p $(NAME)
	cp -r conoha-ojs-sync.php screenshot-1.png readme.txt lang script style tpl vendor $(NAME)
	zip -vr conoha-ojs-sync.zip $(NAME)
clean:
	rm -rf $(NAME) conoha-ojs-sync.zip
