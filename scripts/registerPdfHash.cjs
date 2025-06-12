require('dotenv').config();
const ethers = require("ethers");

const { PRIVATE_KEY, RPC_URL } = process.env;

const CONTRACT_ADDRESS = "0x260B76B3557A846cbF0313Bb525880427FfF5833";
const ABI = [
  {
    "inputs": [
      { "internalType": "uint256", "name": "patientId", "type": "uint256" },
      { "internalType": "string", "name": "pdfHash", "type": "string" }
    ],
    "name": "registerHash",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  }
];

async function registerHash(patientId, hash) {
  try {
    const provider = new ethers.JsonRpcProvider(RPC_URL); // CORREGIDO PARA ethers v6
    const wallet = new ethers.Wallet(PRIVATE_KEY, provider);
    const contract = new ethers.Contract(CONTRACT_ADDRESS, ABI, wallet);

    const tx = await contract.registerHash(patientId, hash);
    console.log("📤 Tx enviada:", tx.hash);

    await tx.wait();
    console.log("✅ Hash registrado en blockchain.");
    console.log(tx.hash);
  } catch (error) {
    console.error("❌ Error:", error.message);
    process.exit(1);
  }
}

const [,, patientId, hash] = process.argv;

if (!patientId || !hash) {
  console.error("Uso: node registerPdfHash.cjs <patientId> <hash>");
  process.exit(1);
}

registerHash(parseInt(patientId), hash);
