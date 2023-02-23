version: CMD=php -v 					## Displays the PHP version
correos: CMD=php /code/cli/correos.php 	## Scrapes Correos.es

version:
	$(call runDockerComposeExec,$(CMD))

correos: check-required-param-province
	$(call runDockerComposeExec,$(CMD),$(province) $(min) $(max))

combine-csv:									## Combine all CSV into one single file
	@cd output && awk '(NR == 1) || (FNR > 1)' province-*.csv > combined.csv