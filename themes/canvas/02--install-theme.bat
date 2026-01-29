IF EXIST "assets.zip" (
	echo "zip found"
) ELSE (
	powershell -Command "Invoke-WebRequest https://cdn.amadeusweb.world/spring/canvas/assets.zip -OutFile assets.zip"
)

IF EXIST "../../../../cdn/spring/" (
	copy "assets.zip" "../../../../cdn/spring/canvas/"
	cd "../../../../cdn/spring/canvas/"
		tar -xzvf assets.zip
) ELSE IF EXIST "assets" (
	echo "assets folder found - if rerunning, backup (in case of changes) then rerun"
) ELSE (
    mkdir "assets"
    cd "assets"
	tar -xzvf ../assets.zip
)

pause
