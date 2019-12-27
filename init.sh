#!/bin/sh
array=( "docker/wp-content/plugins/wp-academic-standards"
        "docker/wp-content/plugins/wp-oer" 
		"docker/wp-content/plugins/wp-curriculum" )
for element in ${array[@]}
do
	if [ ! -d $element ]; then
		echo "**********************************"
		if [ $element == "docker/wp-content/plugins/wp-academic-standards" ]; then
			echo "cloning latest wp-academic-standards"
			echo "**********************************"
			git clone https://github.com/navnorth/wp-academic-standards.git docker/wp-content/plugins/wp-academic-standards
		elif [ $element == "docker/wp-content/plugins/wp-oer" ]; then
			echo "cloning latest wp-oer"
			echo "**********************************"
			git clone --branch mpa-stage https://github.com/navnorth/wp-oer.git docker/wp-content/plugins/wp-oer
		elif [ $element == "docker/wp-content/plugins/wp-curriculum" ]; then
			echo "cloning latest wp-curriculum"
			echo "**********************************"
			git clone --branch mpa-stage https://github.com/navnorth/wp-curriculum.git docker/wp-content/plugins/wp-curriculum
		fi
	else
		echo "**********************************"
		echo "Plugin already exists (" $element ")"
		echo "**********************************"
	fi	
	sleep 2
done
echo "**********************************"
echo "Running docker-compose file"
echo "**********************************"
docker-compose up -d
