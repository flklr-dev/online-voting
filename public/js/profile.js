document.addEventListener('DOMContentLoaded', function() {
    const facultySelect = document.getElementById('faculty');
    const programSelect = document.getElementById('program');
    
    const programsByFaculty = {
        'FaCET': ['BSIT', 'BSCE', 'BSM', 'BSITM'],
        'FALS': ['BSBio', 'BSES', 'BSA'],
        'FBM': ['BSBA', 'BSHM'],
        'FNAHS': ['BSN'],
        'FTED': ['BEED', 'BSED'],
        'FCGE': ['BSC'],
        'FHUSOCOM': ['BAComm', 'BAPolSci']
    };

    facultySelect.addEventListener('change', function() {
        const selectedFaculty = this.value;
        const programs = programsByFaculty[selectedFaculty] || [];
        
        // Clear current options
        programSelect.innerHTML = '<option value="" disabled>Select Program</option>';
        
        // Add new options
        programs.forEach(program => {
            const option = document.createElement('option');
            option.value = program;
            option.textContent = program;
            programSelect.appendChild(option);
        });
        
        programSelect.disabled = programs.length === 0;
    });
}); 