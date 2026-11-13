.PHONY: predict

predict:
	php artisan triage:predict --input="$(IN)" --output="$(OUT)"
