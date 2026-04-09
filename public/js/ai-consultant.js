/* ai-consultant.js */
document.addEventListener('DOMContentLoaded', () => {
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');
    const scannerLine = document.getElementById('scannerLine');
    const aiResultCard = document.getElementById('aiResultCard');
    const analyzeBtn = document.getElementById('analyzeBtn');

    // Handle Click to Upload
    uploadArea.addEventListener('click', () => imageInput.click());

    // Handle File Selection
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
                document.getElementById('uploadPlaceholder').style.display = 'none';
                analyzeBtn.style.display = 'inline-flex';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Handle Analysis
    analyzeBtn.addEventListener('click', async () => {
        const file = imageInput.files[0];
        if (!file) return;

        // UI States
        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = 'جاري التحليل الرقمي...';
        scannerLine.style.display = 'block';
        aiResultCard.style.display = 'none';

        const formData = new FormData();
        formData.append('image', file);
        formData.append('salon_id', 'demo');

        try {
            const response = await fetch('/api/ai/analyze-consultation', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                    // CSRF token is usually needed for POST in Laravel but since this is an API route, 
                    // it might be handled by Sanctum or not required depending on middleware.
                }
            });

            if (!response.ok) throw new Error('Analysis failed');

            const data = await response.json();
            
            // Wait a bit for cinematic effect
            setTimeout(() => {
                displayResults(data);
                scannerLine.style.display = 'none';
                analyzeBtn.disabled = false;
                analyzeBtn.innerHTML = 'تحليل جديد 🔄';
            }, 2500);

        } catch (error) {
            console.error(error);
            alert('عذراً، حدث خطأ أثناء الاتصال بالخادم الذكي.');
            scannerLine.style.display = 'none';
            analyzeBtn.disabled = false;
            analyzeBtn.innerHTML = 'حاول مرة أخرى';
        }
    });

    function displayResults(data) {
        aiResultCard.style.display = 'block';

        if (data.success === false) {
            document.getElementById('aiAnalysisText').innerHTML = data.analysis;
            document.getElementById('aiReasoningText').innerHTML = '';
            document.getElementById('recommendationChips').innerHTML = '';
        } else {
            document.getElementById('aiAnalysisText').innerHTML = data.analysis;
            document.getElementById('aiReasoningText').innerHTML = data.reasoning;

            const chipsContainer = document.getElementById('recommendationChips');
            chipsContainer.innerHTML = '';

            if (Array.isArray(data.recommendations)) {
                data.recommendations.forEach(rec => {
                    const chip = document.createElement('span');
                    chip.className = 'chip';
                    chip.innerHTML = rec;
                    chipsContainer.appendChild(chip);
                });
            }
        }

        // Scroll to results
        aiResultCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
