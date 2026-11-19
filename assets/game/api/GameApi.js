export class GameApi {
    constructor(root) {
        this.startUrl = root.dataset.apiStart;
        this.runsUrl = root.dataset.apiRuns;
        this.startToken = root.dataset.csrfStart;
        this.finishToken = root.dataset.csrfFinish;
    }

    startRun(payload) {
        return this.post(this.startUrl, payload, this.startToken);
    }

    finishRun(id, payload) {
        return this.post(`${this.runsUrl}/${id}/finish`, payload, this.finishToken);
    }

    async post(url, payload, csrfToken) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify(payload),
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(result.detail ?? result.title ?? `Erreur serveur (${response.status}).`);
        return result;
    }
}
