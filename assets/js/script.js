(function () {
	const addRowButton = document.getElementById('add-row');
	const data = [];
	let totalLinksCount = 0;

	function updateProgressBar(width) {
		document.getElementById('progress-bar').style.width = `${width.toFixed(2)}%`;
	}

	function addRow() {
		const rows = document.getElementsByClassName('input-row');
		const template = document.getElementById('row-template').innerHTML;
		const newRow = document.createElement('tr');
		const wrapper = document.getElementById('rows-wrapper');
		const rowIndex = rows.length + 1 || 1;

		newRow.setAttribute('class', 'input-row');
		newRow.innerHTML = template.replace(/{{index}}/g, rowIndex.toString());

		wrapper.appendChild(newRow);

		wrapper.scrollTo({
			top: wrapper.scrollHeight,
			behavior: 'smooth',
		});
	}

	function addReportFormRow(dataSet) {
		const rowTemplate = document.getElementById('report-template').innerHTML;
		const reportForm = document.getElementById('report-form');

		if (!reportForm || !rowTemplate) {
			return;
		}

		const index = (reportForm.getElementsByClassName('report-row').length + 1 || 1).toString();

		const reportRow = document.createElement('div');
		reportRow.classList.add('report-row');
		reportRow.innerHTML = rowTemplate.replace(/{{index}}/g, index);

		for (let key in dataSet) {
			const inputField = reportRow.querySelector(`.input-${key.toString()}`)

			if (inputField) {
				inputField.value = dataSet[key].toString() || '';
			}
		}

		reportForm.appendChild(reportRow);
	}

	function fillScanFormRow(dataSet) {
		for (let key in dataSet) {
			const inputField = this.querySelector(`.link-${key.toString()}`);

			if (inputField) {
				inputField.value = dataSet[key].toString() || ' ';
			}
		}

		[...this.querySelectorAll('input.active')].forEach(inputField => {
			inputField.setAttribute('readonly', 'readonly')
		});

		addReportFormRow(dataSet);
	}

	function makeRequest(requestURI = '', args = {}) {
		return fetch(`${document.URL.replace(/\/$/, '')}/${requestURI}`, args);
	}

	function removeRow() {
		if (!confirm('Are you sure?')) {
			return true;
		}

		const rows = [...document.getElementsByClassName('input-row')];

		if (rows.length <= 1) {
			return true;
		}

		this.closest('.input-row').remove();

		refreshNames();
	}

	function refreshNames() {
		const rows = [...document.getElementsByClassName('input-row')];

		if (!rows.length) {
			return true;
		}

		rows.forEach(function (item, index) {
			const inputs = [...item.querySelectorAll('input.active')];

			if (!inputs.length) {
				return;
			}

			inputs.forEach(function (input) {
				const nameAttr = input.getAttribute('name').replace(/\d/, index + 1);
				input.setAttribute('name', nameAttr);
			});

			item.firstElementChild.innerText = (index + 1).toString();
		});
	}

	function scanByRow() {
		if (!Array.isArray(data) || !data.length) {
			document.getElementById('download-report').disabled = false;
			document.querySelector('table').classList.remove('in-progress');

			const progressBar = document.getElementById('progress-bar');

			progressBar.classList.remove('progress-bar-striped');
			progressBar.classList.remove('progress-bar-animated');

			return;
		}

		const { acceptor, donor, item } = data.shift();

		const formData = new FormData();

		formData.append('action', 'scan');
		formData.append('acceptor', acceptor);
		formData.append('donor', donor);

		makeRequest(``, {
			method: 'POST',
			body: formData,
		})
			.then(response => response.json())
			.then(response => {
				if (!!response.error) {
					//item.classList.add('is-invalid');
				} else {
					fillScanFormRow.call(item, response);
				}

				updateProgressBar(100 - (data.length / totalLinksCount * 100));

				setTimeout(scanByRow, 1000);
			})
			.catch(error => console.error(error.message));
	}

	function deleteReport() {
		const formData = new FormData();
		formData.append('action', 'clean');

		makeRequest('', {
			method: 'POST',
			body: formData,
		})
			.then(response => console.log(response))
			.catch(error => console.error(error.message));
	}

	function prepareLink(linksString) {
		return linksString
			.toString()
			.replaceAll('%7C', '|')
			.split('|')
			.filter(link => !!link) || [];
	}

	function generateReport() {
		const form = document.getElementById('report-form');
		const formData = new FormData(form);

		formData.append('action', 'report');

		makeRequest('', {
			method: 'POST',
			body: formData,
		})
			.then(response => response.json())
			.then(json => {
				if (!!json.file) {
					const link = document.createElement('a');
					link.href = json.file;
					document.body.appendChild(link);
					link.click();

					setTimeout(() => {
						deleteReport();
						link.remove();
					}, 1000);
				}
			})
			.catch(error => console.error(error.message));
	}

	document.addEventListener('click', function (e) {
		const target = e.target;

		if (target.classList.contains('remove-row')) {
			removeRow.call(target, e);
		} else if (target.classList.contains('download-report')) {
			generateReport.call(target, e);
		}
	}, { passive: true });

	document.querySelector('form').addEventListener('submit', function (e) {
		e.preventDefault();

		document.getElementById('submit').disabled = true;

		[...document.getElementsByClassName('remove-row')].forEach((button) => {
			button.disabled = true;
		});

		const rows = [...document.getElementsByClassName('input-row')];

		rows.forEach(item => {
			const acceptor = prepareLink(item.querySelector('input.acceptor').value);
			const donor = prepareLink(item.querySelector('input.anchor').value);

			if (Array.isArray(donor) && donor.length > 1) {
				donor.forEach((donorLink) => {
					data.push({
						acceptor,
						donor: donorLink,
						item
					});
				});
			} else {
				data.push({
					acceptor,
					donor,
					item
				});
			}
		});

		document.querySelector('table').classList.add('in-progress');

		totalLinksCount = data.length;

		scanByRow();
	}, false);
	window.addEventListener('load', addRow, { passive: true })
})();