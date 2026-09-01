function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')      // replace spaces with -
        .replace(/[^\w\-]+/g, ''); // remove special chars
}

document.addEventListener('DOMContentLoaded', function() {
    // Select all input fields that should generate slugs
    const slugPairs = [
        { inputName: 'name', slugName: 'slug' },   // Product form
        { inputName: 'title', slugName: 'slug' }   // Other form
    ];

    slugPairs.forEach(pair => {
        const input = document.querySelector(`input[name="${pair.inputName}"]`);
        const slugInput = document.querySelector(`input[name="${pair.slugName}"]`);

        if (input && slugInput) {
            input.addEventListener('input', function() {
                slugInput.value = generateSlug(this.value);
            });

            slugInput.addEventListener('input', function() {
                this.value = this.value.replace(/\s+/g, '-');
            });
        }
    });
});