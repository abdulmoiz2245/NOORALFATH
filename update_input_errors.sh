#!/bin/bash

# List of files to update
files=(
    "resources/js/pages/vendors/Edit.vue"
    "resources/js/pages/vendors/Create.vue"
    "resources/js/pages/company/Edit.vue"
    "resources/js/pages/company/Create.vue"
    "resources/js/pages/expenses/Edit.vue"
    "resources/js/pages/expenses/Create.vue"
    "resources/js/pages/companies/Edit.vue"
    "resources/js/pages/clients/Create.vue"
    "resources/js/pages/quotations/Edit.vue"
    "resources/js/pages/quotations/Create.vue"
    "resources/js/pages/products/Edit.vue"
    "resources/js/pages/products/Create.vue"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "Processing $file..."
        
        # Add InputError import if not already present
        if ! grep -q "import InputError" "$file"; then
            sed -i 's/import { Input } from.*input.*;/&\nimport InputError from '\''@\/components\/InputError.vue'\'';/' "$file"
        fi
        
        # Replace inline error messages with InputError components
        sed -i 's/<p v-if="form\.errors\.\([^"]*\)" class="text-sm text-red-500">{{ form\.errors\.\1 }}<\/p>/<InputError :message="form.errors.\1" \/>/g' "$file"
        
        echo "Updated $file"
    else
        echo "File $file not found, skipping..."
    fi
done

echo "Done updating all files!"
