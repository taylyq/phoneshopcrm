document.documentElement.classList.add('js');

const uploadForm = document.querySelector('[data-upload-form]');

if (uploadForm) {
    const statusBox = uploadForm.querySelector('[data-upload-status]');
    const title = uploadForm.querySelector('[data-upload-title]');
    const detail = uploadForm.querySelector('[data-upload-detail]');
    const percent = uploadForm.querySelector('[data-upload-percent]');
    const bar = uploadForm.querySelector('[data-upload-bar]');
    const fileList = uploadForm.querySelector('[data-upload-files]');
    const submit = uploadForm.querySelector('[data-upload-submit]');
    const fileInputs = [...uploadForm.querySelectorAll('input[type="file"]')];

    const bytes = (value) => {
        if (!value) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const index = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1);
        return `${(value / (1024 ** index)).toFixed(index ? 1 : 0)} ${units[index]}`;
    };

    const selectedFiles = () => fileInputs.flatMap((input) => [...input.files].map((file) => ({
        label: input.closest('label')?.childNodes[0]?.textContent?.trim() || 'File',
        name: file.name,
        size: file.size,
    })));

    const setProgress = (value, nextTitle, nextDetail) => {
        const safeValue = Math.max(0, Math.min(100, Math.round(value)));
        statusBox.hidden = false;
        title.textContent = nextTitle;
        detail.textContent = nextDetail;
        percent.textContent = `${safeValue}%`;
        bar.style.width = `${safeValue}%`;
    };

    const renderFiles = () => {
        const files = selectedFiles();
        fileList.innerHTML = '';
        statusBox.classList.remove('upload-status--error');
        files.forEach((file) => {
            const item = document.createElement('li');
            item.innerHTML = `<span>${file.label}: ${file.name}</span><strong>${bytes(file.size)}</strong>`;
            fileList.append(item);
        });
        if (files.length) {
            setProgress(0, 'Ready to upload', `${files.length} file${files.length === 1 ? '' : 's'} selected`);
        }
    };

    fileInputs.forEach((input) => input.addEventListener('change', renderFiles));

    uploadForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const xhr = new XMLHttpRequest();
        const formData = new FormData(uploadForm);
        const files = selectedFiles();
        const originalText = submit.textContent;

        statusBox.classList.remove('upload-status--error');
        submit.disabled = true;
        submit.textContent = 'Saving...';
        setProgress(files.length ? 2 : 45, files.length ? 'Uploading files' : 'Saving ticket', files.length ? 'Starting transfer...' : 'No files selected.');

        xhr.open('POST', uploadForm.action);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener('progress', (progress) => {
            if (!progress.lengthComputable) {
                setProgress(35, 'Uploading files', 'Transfer in progress...');
                return;
            }

            const uploaded = bytes(progress.loaded);
            const total = bytes(progress.total);
            setProgress((progress.loaded / progress.total) * 85, 'Uploading files', `${uploaded} of ${total} transferred`);
        });

        xhr.addEventListener('load', () => {
            let payload = null;
            try {
                payload = JSON.parse(xhr.responseText);
            } catch {
                payload = null;
            }

            if (xhr.status >= 200 && xhr.status < 300 && payload?.ok) {
                setProgress(100, 'Upload complete', 'Ticket saved. Opening dashboard...');
                window.location.href = payload.redirect || '/tickets';
                return;
            }

            const errors = payload?.errors?.length ? payload.errors : ['Upload failed. Check diagnostics and try again.'];
            setProgress(100, 'Upload needs attention', errors[0]);
            statusBox.classList.add('upload-status--error');
            submit.disabled = false;
            submit.textContent = originalText;
        });

        xhr.addEventListener('error', () => {
            setProgress(100, 'Connection interrupted', 'The upload did not finish. Try again after checking your connection.');
            statusBox.classList.add('upload-status--error');
            submit.disabled = false;
            submit.textContent = originalText;
        });

        xhr.send(formData);
    });
}
